<?php

declare(strict_types=1);

namespace App\Tests\Unit\IdentityAccess\User\Application\Service\AppService;

use App\IdentityAccess\User\Application\Service\AppService\UserService;
use App\IdentityAccess\User\Application\Service\UserQueryServiceInterface;
use App\IdentityAccess\User\Domain\Exception\IdentityException;
use App\IdentityAccess\User\Domain\Port\UserRepositoryInterface;
use App\IdentityAccess\User\Domain\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Throwable;

#[CoversClass(UserService::class)]
class UserServiceTest extends TestCase
{
    private const string TEST_LOGIN = 'test@example.com';
    private const string TEST_PASSWORD = 'test-password';
    private const int TEST_USER_ID = 1;

    private MockObject|UserRepositoryInterface $userRepository;
    private MockObject|UserQueryServiceInterface $userQueryService;
    private UserService $userService;

    /**
     * @throws IdentityException
     * @throws Throwable
     */
    public function testCreateUserSuccessfully(): void
    {
        $this->userQueryService
            ->method('findByLogin')
            ->with(self::TEST_LOGIN)
            ->willReturn(null);

        $this->userRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->isType('array'));

        $this->userService->createUser(self::TEST_LOGIN, self::TEST_PASSWORD);
    }

    /**
     * @throws Throwable
     * @throws Exception
     */
    public function testCreateUserWithExistingLoginThrowsException(): void
    {
        $existingUser = $this->createMock(User::class);

        $this->userRepository
            ->expects($this->once())
            ->method('findByLogin')
            ->with(self::TEST_LOGIN)
            ->willReturn($existingUser);

        $this->userRepository
            ->expects($this->never())
            ->method('save');

        $this->expectException(IdentityException::class);
        $this->expectExceptionMessage('This user already exists!');

        $this->userService->createUser(self::TEST_LOGIN, self::TEST_PASSWORD);
    }

    /**
     * @throws IdentityException
     * @throws Exception
     */
    public function testDeleteUserSuccessfully(): void
    {
        $user = $this->createMock(User::class);
        $user
            ->method('getId')
            ->willReturn(self::TEST_USER_ID);

        $this->userRepository
            ->method('findUser')
            ->with(self::TEST_USER_ID)
            ->willReturn($user);

        $this->userRepository
            ->expects($this->once())
            ->method('delete')
            ->with($this->isType('array'));

        $this->userService->deleteUser(self::TEST_USER_ID);
    }

    public function testDeleteNonExistentUserThrowsException(): void
    {
        $this->userRepository
            ->method('findUser')
            ->with(self::TEST_USER_ID)
            ->willReturn(null);

        $this->expectException(IdentityException::class);
        $this->expectExceptionMessage('This user does not exist!');

        $this->userService->deleteUser(self::TEST_USER_ID);
    }

    public function testRemoveAllUsers(): void
    {
        $this->userRepository
            ->expects($this->once())
            ->method('removeAll');

        $this->userService->removeAll();
    }

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->userQueryService = $this->createMock(UserQueryServiceInterface::class);
        $this->userService = new UserService($this->userRepository);
    }
}
