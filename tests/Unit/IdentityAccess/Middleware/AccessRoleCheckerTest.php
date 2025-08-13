<?php

declare(strict_types=1);

namespace App\Tests\Unit\IdentityAccess\Middleware;

use App\IdentityAccess\Access\Application\Service\AssignmentsServiceInterface;
use App\IdentityAccess\AuthService\AuthenticationService;
use App\IdentityAccess\AuthService\AuthorizationService;
use App\IdentityAccess\Middleware\AccessRoleChecker;
use App\IdentityAccess\User\Application\Service\UserQueryServiceInterface;
use App\IdentityAccess\User\Domain\User;
use App\IdentityAccess\User\Infrastructure\Authentication\Identity;
use App\IdentityAccess\User\Infrastructure\Authentication\IdentityRepositoryInterface;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionClass;
use RuntimeException;
use Yiisoft\Auth\IdentityInterface;
use Yiisoft\Http\Status;
use Yiisoft\Session\SessionInterface;
use Yiisoft\User\CurrentUser;

#[CoversClass(AccessRoleChecker::class)]
class AccessRoleCheckerTest extends TestCase
{
    private AuthenticationService $authenticationService;
    private AuthorizationService $authorizationService;
    private ResponseFactoryInterface $responseFactory;
    private ServerRequestInterface $request;
    private RequestHandlerInterface $handler;
    private ResponseInterface $response;
    private Identity $identity;
    private User $user;

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
        $user = $this->createMock(IdentityInterface::class);
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
        $roleProperty->setAccessible(true);
        $this->assertNull($roleProperty->getValue($checker));

        // Проверяем, что в новом объекте роль установлена
        $newReflection = new ReflectionClass($newChecker);
        $newRoleProperty = $newReflection->getProperty('role');
        $newRoleProperty->setAccessible(true);
        $this->assertSame('admin', $newRoleProperty->getValue($newChecker));
    }

    /**
     * @throws Exception
     */
    #[Override]
    protected function setUp(): void
    {
        $user = new User('admin', 'admin');
        $reflection = new ReflectionClass($user);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($user, 1);
        $this->user = $user;

        $this->identity = new Identity($this->user);
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
