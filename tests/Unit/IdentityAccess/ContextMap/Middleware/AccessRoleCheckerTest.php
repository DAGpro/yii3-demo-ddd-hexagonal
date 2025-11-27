<?php

declare(strict_types=1);

namespace App\Tests\Unit\IdentityAccess\ContextMap\Middleware;

use App\IdentityAccess\Access\Slice\Service\AssignmentsServiceInterface;
use App\IdentityAccess\ContextMap\AuthService\AuthenticationService;
use App\IdentityAccess\ContextMap\AuthService\AuthorizationService;
use App\IdentityAccess\ContextMap\Middleware\AccessRoleChecker;
use App\IdentityAccess\User\Domain\User;
use App\IdentityAccess\User\Infrastructure\Authentication\Identity;
use App\IdentityAccess\User\Infrastructure\Authentication\IdentityRepositoryInterface;
use App\IdentityAccess\User\Slice\User\UserQueryServiceInterface;
use App\Tests\UnitTester;
use Codeception\Test\Unit;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionClass;
use RuntimeException;
use Yiisoft\Http\Status;
use Yiisoft\Session\SessionInterface;
use Yiisoft\User\CurrentUser;

#[CoversClass(AccessRoleChecker::class)]
class AccessRoleCheckerTest extends Unit
{
    private static ?User $user = null;

    protected UnitTester $tester;

    private AuthenticationService $authenticationService;

    private AuthorizationService $authorizationService;

    private ResponseFactoryInterface&MockObject $responseFactory;

    private ServerRequestInterface&MockObject $request;

    private RequestHandlerInterface&MockObject $handler;

    private ResponseInterface&MockObject $response;

    private Identity $identity;

    private IdentityRepositoryInterface&MockObject $identityRepository;

    private AssignmentsServiceInterface&MockObject $assignmentsService;

    public function testProcessWithoutRoleThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Role not set.');

        $this->identityRepository
            ->expects($this->once())
            ->method('findIdentity')
            ->willReturn($this->identity);

        $checker = new AccessRoleChecker(
            $this->responseFactory,
            $this->authenticationService,
            $this->authorizationService,
        );

        $checker->process($this->request, $this->handler);
    }

    public function testProcessWithoutAuthenticatedUserThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Log in to the site');

        $checker = new AccessRoleChecker(
            $this->responseFactory,
            $this->authenticationService,
            $this->authorizationService,
        );

        $checker = $checker->withRole('admin');
        $checker->process($this->request, $this->handler);
    }

    /**
     * @throws Exception
     */
    public function testProcessWithUnauthorizedRoleReturnsForbidden(): void
    {
        $this->identityRepository
            ->expects($this->once())
            ->method('findIdentity')
            ->willReturn($this->identity);

        $this->assignmentsService
            ->method('userHasRole')
            ->with('1', 'admin')
            ->willReturn(false);

        $this->response
            ->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(403);

        $this->responseFactory
            ->expects($this->once())
            ->method('createResponse')
            ->with(Status::FORBIDDEN)
            ->willReturn($this->response);

        $checker = new AccessRoleChecker(
            $this->responseFactory,
            $this->authenticationService,
            $this->authorizationService,
        );

        $checker = $checker->withRole('admin');
        $result = $checker->process($this->request, $this->handler);

        $this->assertSame($this->response, $result);
        $this->assertSame(403, $result->getStatusCode());
    }

    /**
     * @throws Exception
     */
    public function testProcessWithAuthorizedRoleCallsHandler(): void
    {
        $handlerResponse = $this->createMock(ResponseInterface::class);

        $this->identityRepository
            ->expects($this->once())
            ->method('findIdentity')
            ->willReturn($this->identity);

        $checker = new AccessRoleChecker(
            $this->responseFactory,
            $this->authenticationService,
            $this->authorizationService,
        );

        $this->handler
            ->expects($this->once())
            ->method('handle')
            ->willReturn($handlerResponse);

        $this->assignmentsService
            ->expects($this->once())
            ->method('userHasRole')
            ->with('1', 'admin')
            ->willReturn(true);

        $checker = $checker->withRole('admin');
        $result = $checker->process($this->request, $this->handler);

        $this->assertSame($handlerResponse, $result);
    }

    public function testWithRoleReturnsNewInstanceWithRole(): void
    {
        $checker = new AccessRoleChecker(
            $this->responseFactory,
            $this->authenticationService,
            $this->authorizationService,
        );

        $newChecker = $checker->withRole('admin');

        $this->assertNotSame($checker, $newChecker);

        // Проверяем, что оригинальный объект не изменился
        $reflection = new ReflectionClass($checker);
        $roleProperty = $reflection->getProperty('role');
        $this->assertNull($roleProperty->getValue($checker));

        // Проверяем, что в новом объекте роль установлена
        $newReflection = new ReflectionClass($newChecker);
        $newRoleProperty = $newReflection->getProperty('role');
        $this->assertSame('admin', $newRoleProperty->getValue($newChecker));
    }

    /**
     * @throws Exception
     */
    #[Override]
    protected function _before(): void
    {
        if (self::$user === null) {
            $user = new User('admin', 'admin');
            $reflection = new ReflectionClass($user);
            $property = $reflection->getProperty('id');
            $property->setValue($user, 1);
            self::$user = $user;
        }

        $this->identity = new Identity(self::$user);
        $this->identityRepository = $this->createMock(IdentityRepositoryInterface::class);

        $currentUser = new CurrentUser(
            $this->identityRepository,
            $this->createMock(EventDispatcherInterface::class),
        );

        $session = $this->createMock(SessionInterface::class);
        $session
            ->method('get')
            ->with('__auth_id')
            ->willReturn(1);
        $currentUser = $currentUser->withSession($session);

        $this->authenticationService = new AuthenticationService(
            $currentUser,
            $this->createMock(UserQueryServiceInterface::class),
            $this->createMock(IdentityRepositoryInterface::class),
        );

        $this->authorizationService = new AuthorizationService(
            $this->assignmentsService = $this->createMock(AssignmentsServiceInterface::class),
        );

        $this->responseFactory = $this->createMock(ResponseFactoryInterface::class);
        $this->request = $this->createMock(ServerRequestInterface::class);
        $this->handler = $this->createMock(RequestHandlerInterface::class);
        $this->response = $this->createMock(ResponseInterface::class);

        // Настраиваем responseFactory для создания ответа
        $this->responseFactory
            ->method('createResponse')
            ->willReturn($this->response);
    }
}
