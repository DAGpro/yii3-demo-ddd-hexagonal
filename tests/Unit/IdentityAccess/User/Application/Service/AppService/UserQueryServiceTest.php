<?php

declare(strict_types=1);

namespace App\Tests\Unit\IdentityAccess\User\Application\Service\AppService;

use App\IdentityAccess\User\Application\Service\AppService\UserQueryService;
use App\IdentityAccess\User\Domain\Port\UserRepositoryInterface;
use App\IdentityAccess\User\Domain\User;
use App\Tests\UnitTester;
use Codeception\Test\Unit;
use Cycle\Database\Driver\DriverInterface;
use Cycle\ORM\Select;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use Yiisoft\Data\Cycle\Reader\EntityReader;

#[CoversClass(UserQueryService::class)]
class UserQueryServiceTest extends Unit
{
    private const int TEST_USER_ID = 1;

    private const string TEST_LOGIN = 'test@example.com';

    protected UnitTester $tester;

    private UserRepositoryInterface&MockObject $userRepository;

    private UserQueryService $userQueryService;

    private Select&MockObject $select;

    public function testFindAllPreloaded(): void
    {
        $selectResult = [
            new User('user1', 'password1'),
            new User('user2', 'password2'),
            new User('user3', 'password3'),
        ];

        $this->select
            ->method('__call')
            ->willReturnCallback(
                function (string $method, array $arguments) {
                    if ($method === 'getDriver') {
                        $driverMock = $this->createMock(DriverInterface::class);
                        $driverMock
                            ->expects($this->once())
                            ->method('getType')
                            ->willReturn('SQLite');
                        return $driverMock;
                    }
                    return $this->select;
                },
            );
        $this->select
            ->method('fetchAll')
            ->willReturnCallback(
                fn() => $selectResult,
            );

        $this->userRepository
            ->expects($this->once())
            ->method('findAllPreloaded')
            ->willReturn(new EntityReader($this->select));

        $dataReader = $this->userQueryService->findAllPreloaded();

        $sortDataReader = $dataReader->getSort();
        $this->assertNull($sortDataReader);
        $this->assertEquals($selectResult, $dataReader->read());
    }

    /**
     * @throws Exception
     */
    public function testGetUser(): void
    {
        $user = $this->createMock(User::class);

        $this->userRepository
            ->expects($this->once())
            ->method('findUser')
            ->with(self::TEST_USER_ID)
            ->willReturn($user);

        $result = $this->userQueryService->getUser(self::TEST_USER_ID);

        $this->assertSame($user, $result);
    }

    public function testGetUserReturnsNullWhenNotFound(): void
    {
        $this->userRepository
            ->expects($this->once())
            ->method('findUser')
            ->with(self::TEST_USER_ID)
            ->willReturn(null);

        $result = $this->userQueryService->getUser(self::TEST_USER_ID);

        $this->assertNull($result);
    }

    public function testFindByLogin(): void
    {
        $user = $this->createMock(User::class);

        $this->userRepository
            ->expects($this->once())
            ->method('findByLogin')
            ->with(self::TEST_LOGIN)
            ->willReturn($user);

        $result = $this->userQueryService->findByLogin(self::TEST_LOGIN);

        $this->assertSame($user, $result);
    }

    public function testFindByLoginReturnsNullWhenNotFound(): void
    {
        $this->userRepository
            ->expects($this->once())
            ->method('findByLogin')
            ->with(self::TEST_LOGIN)
            ->willReturn(null);

        $result = $this->userQueryService->findByLogin(self::TEST_LOGIN);

        $this->assertNull($result);
    }

    /**
     * @throws Exception
     */
    public function testGetUsers(): void
    {
        $userIds = [1, 2, 3];
        $users = [
            $this->createUserMock(1, 'user1@example.com'),
            $this->createUserMock(2, 'user2@example.com'),
            $this->createUserMock(3, 'user3@example.com'),
        ];

        $this->userRepository
            ->expects($this->once())
            ->method('getUsers')
            ->with($userIds)
            ->willReturn($users);

        $result = $this->userQueryService->getUsers($userIds);

        $this->assertSame($users, $result);
    }

    public function testGetUsersWithEmptyArray(): void
    {
        $userIds = [];
        $emptyUsers = [];

        $this->userRepository
            ->expects($this->once())
            ->method('getUsers')
            ->with($userIds)
            ->willReturn($emptyUsers);

        $result = $this->userQueryService->getUsers($userIds);

        $this->assertSame($emptyUsers, $result);
        $this->assertCount(0, $result);
    }

    /**
     * @throws Exception
     */
    #[Override]
    protected function _before(): void
    {
        $this->select = $this->createMock(Select::class);
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->userQueryService = new UserQueryService($this->userRepository);
    }

    /**
     * @throws Exception
     */
    private function createUserMock(int $id, string $login): User
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn($id);
        $user->method('getLogin')->willReturn($login);
        return $user;
    }
}
