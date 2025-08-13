<?php

declare(strict_types=1);

namespace App\Tests\Unit\IdentityAccess\AuthService;

use App\IdentityAccess\AuthService\AuthenticationService;
use App\IdentityAccess\User\Application\Service\UserQueryServiceInterface;
use App\IdentityAccess\User\Domain\User;
use App\IdentityAccess\User\Infrastructure\Authentication\AuthenticationException;
use App\IdentityAccess\User\Infrastructure\Authentication\Identity;
use App\IdentityAccess\User\Infrastructure\Authentication\IdentityRepositoryInterface;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Throwable;
use Yiisoft\User\CurrentUser;
use Yiisoft\User\Event\AfterLogin;
use Yiisoft\User\Event\BeforeLogin;
use Yiisoft\User\Method\WebAuth;

#[CoversClass(AuthenticationService::class)]
final class AuthenticationServiceTest extends TestCase
{
    private AuthenticationService $authService;
    private CurrentUser $currentUser;
    private MockObject|UserQueryServiceInterface $userQueryService;
    private MockObject|IdentityRepositoryInterface $identityRepository;
    private MockObject|WebAuth $webAuth;

    private EventDispatcherInterface|MockObject $eventDispatcher;

    /**
     * @throws Throwable
     * @throws Exception
     * @throws AuthenticationException
     */
    public function testLoginSuccess(): void
    {
        $login = 'testuser';
        $password = 'password';
        $user = $this->createMock(User::class);

        $identity = $this->createMock(Identity::class);
        $identity
            ->expects($this->once())
            ->method('getId')
            ->willReturn('1');

        $user
            ->method('validatePassword')
            ->with($password)
            ->willReturn(true);

        $this->userQueryService
            ->expects($this->once())
            ->method('findByLogin')
            ->with($login)
            ->willReturn($user);

        $this->identityRepository
            ->expects($this->once())
            ->method('findOrCreate')
            ->with($user)
            ->willReturn($identity);

        $this->eventDispatcher
            ->expects($this->exactly(2))
            ->method('dispatch')
            ->willReturnCallback(
                static function (BeforeLogin|AfterLogin $event): object {
                    return $event;
                },
            );

        $result = $this->authService->login($login, $password);

        $this->assertSame($identity, $result);
    }

    /**
     * @throws Throwable
     */
    public function testLoginWithInvalidCredentials(): void
    {
        $login = 'wronguser';
        $password = 'wrongpass';

        $this->userQueryService
            ->method('findByLogin')
            ->with($login)
            ->willReturn(null);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Login or password incorrect!');

        $this->authService->login($login, $password);
    }

    /**
     * @throws Throwable
     * @throws Exception
     */
    public function testLoginFails(): void
    {
        $login = 'testuser';
        $password = 'password';
        $user = $this->createMock(User::class);
        $identity = $this->createMock(Identity::class);

        $user
            ->method('validatePassword')
            ->with($password)
            ->willReturn(true);

        $this->userQueryService
            ->method('findByLogin')
            ->with($login)
            ->willReturn($user);

        $this->identityRepository
            ->method('findOrCreate')
            ->with($user)
            ->willReturn($identity);

        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->willReturnCallback(
                static function (BeforeLogin $event): object {
                    $event->invalidate();
                    return $event;
                },
            );

        $authService = new AuthenticationService(
            $this->currentUser,
            $this->userQueryService,
            $this->identityRepository,
        );

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Login failed, please try again!');

        $authService->login($login, $password);
    }

    /**
     * @throws Throwable
     * @throws Exception
     */
    public function testLogout(): void
    {
        $identity = $this->createMock(Identity::class);

        $this->eventDispatcher
            ->expects($this->exactly(4))
            ->method('dispatch')
            ->willReturnCallback(
                static function (object $event): object {
                    return $event;
                },
            );

        $this->currentUser->login($identity);

        $identity
            ->expects($this->once())
            ->method('regenerateCookieLoginKey');

        $this->identityRepository
            ->expects($this->once())
            ->method('save')
            ->with($identity);


        $result = $this->authService->logout();
        $this->assertTrue($result);
    }

    /**
     * @throws Throwable
     */
    public function testLogoutWhenNoIdentity(): void
    {
        $this->identityRepository
            ->expects($this->never())
            ->method('save');

        $this->eventDispatcher
            ->expects($this->never())
            ->method('dispatch');

        $result = $this->authService->logout();
        $this->assertFalse($result);
    }

    /**
     * @throws Exception
     */
    public function testGetUserWhenAuthenticated(): void
    {
        $user = $this->createMock(User::class);
        $identity = $this->createMock(Identity::class);

        $identity
            ->method('getUser')
            ->willReturn($user);

        $this->eventDispatcher
            ->expects($this->exactly(2))
            ->method('dispatch')
            ->willReturnCallback(
                static function (object $event): object {
                    return $event;
                },
            );

        $this->currentUser->login($identity);
        $result = $this->authService->getUser();

        $this->assertSame($user, $result);
    }

    public function testGetUserWhenGuest(): void
    {
        $result = $this->authService->getUser();
        $this->assertNull($result);
    }

    public function testIsGuest(): void
    {
        $result = $this->authService->isGuest();
        $this->assertTrue($result);
    }

    /**
     * @throws Exception
     */
    #[Override]
    protected function setUp(): void
    {
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->identityRepository = $this->createMock(IdentityRepositoryInterface::class);
        
        $this->currentUser = new CurrentUser(
            $this->identityRepository,
            $this->eventDispatcher,
        );

        $this->userQueryService = $this->createMock(UserQueryServiceInterface::class);

        $this->authService = new AuthenticationService(
            $this->currentUser,
            $this->userQueryService,
            $this->identityRepository,
        );
    }
}
