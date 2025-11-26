<?php

declare(strict_types=1);

namespace App\Tests\Unit\IdentityAccess\ContextMap\Middleware;

use App\IdentityAccess\Access\Slice\Service\AssignmentsServiceInterface;
use App\IdentityAccess\ContextMap\AuthService\AuthenticationService;
use App\IdentityAccess\ContextMap\AuthService\AuthorizationService;
use App\IdentityAccess\ContextMap\Middleware\AccessPermissionChecker;
use App\IdentityAccess\User\Domain\User;
use App\IdentityAccess\User\Infrastructure\Authentication\Identity;
use App\IdentityAccess\User\Infrastructure\Authentication\IdentityRepositoryInterface;
use App\IdentityAccess\User\Slice\User\Service\UserQueryServiceInterface;
use App\Tests\UnitTester;
use Codeception\Test\Unit;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
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

#[CoversClass(AccessPermissionChecker::class)]
final class AccessPermissionCheckerTest extends Unit
{
    private static ?User $user = null;

    protected UnitTester $tester;

    private AccessPermissionChecker $middleware;

    private AuthenticationService $authenticationService;

    private AuthorizationService $authorizationService;

    private ResponseFactoryInterface&MockObject $responseFactory;

    private RequestHandlerInterface&MockObject $handler;

    private ServerRequestInterface&MockObject $request;

    private ResponseInterface&MockObject $response;

    private Identity $identity;

    private IdentityRepositoryInterface&MockObject $identityRepository;

    private AssignmentsServiceInterface&MockObject $assignmentsService;

    public function testProcessWithoutAuthenticatedUserThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Log in to the site');

        $this->middleware
            ->withPermission('test_permission')
            ->process($this->request, $this->handler);
    }

    public function testProcessWithoutPermissionThrowsException(): void
    {
        $this->identityRepository
            ->expects($this->once())
            ->method('findIdentity')
            ->willReturn($this->identity);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Permission not set.');

        $this->middleware->process($this->request, $this->handler);
    }

    public function testProcessWithPermissionDeniedReturnsForbiddenResponse(): void
    {
        $this->identityRepository
            ->expects($this->once())
            ->method('findIdentity')
            ->willReturn($this->identity);

        $this->assignmentsService
            ->method('userHasPermission')
            ->with('1', 'test_permission')
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

        $result = $this->middleware
            ->withPermission('test_permission')
            ->process($this->request, $this->handler);

        $this->assertSame($this->response, $result);
        $this->assertSame(403, $result->getStatusCode());
    }

    public function testProcessWithPermissionAllowedCallsHandler(): void
    {
        $response = $this->createMock(ResponseInterface::class);

        $this->identityRepository
            ->expects($this->once())
            ->method('findIdentity')
            ->willReturn($this->identity);

        $this->handler
            ->expects($this->once())
            ->method('handle')
            ->with($this->request)
            ->willReturn($response);

        $this->assignmentsService
            ->expects($this->once())
            ->method('userHasPermission')
            ->with('1', 'test_permission')
            ->willReturn(true);

        $result = $this->middleware
            ->withPermission('test_permission')
            ->process($this->request, $this->handler);

        $this->assertSame($response, $result);
    }

    public function testWithRoleReturnsNewInstanceWithRole(): void
    {
        $checker = new AccessPermissionChecker(
            $this->responseFactory,
            $this->authenticationService,
            $this->authorizationService,
        );

        $newChecker = $checker->withPermission('test_permission');

        $this->assertNotSame($checker, $newChecker);

        // We check that the original object has not changed
        $reflection = new ReflectionClass($checker);
        $permissionProperty = $reflection->getProperty('permission');
        $this->assertNull($permissionProperty->getValue($checker));

        // We check that in the new facility the role is established
        $newReflection = new ReflectionClass($newChecker);
        $newPermissionProperty = $newReflection->getProperty('permission');
        $this->assertSame('test_permission', $newPermissionProperty->getValue($newChecker));
    }

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

        $this->responseFactory
            ->method('createResponse')
            ->willReturn($this->response);

        $this->middleware = new AccessPermissionChecker(
            $this->responseFactory,
            $this->authenticationService,
            $this->authorizationService,
        );
    }
}
