<?php

declare(strict_types=1);

namespace App\Tests\Unit\IdentityAccess\User\Slice\User;

use App\IdentityAccess\User\Domain\User;
use App\IdentityAccess\User\Slice\User\UserRepository;
use App\Tests\UnitTester;
use Codeception\Test\Unit;
use Cycle\Database\DatabaseInterface;
use Cycle\Database\Driver\DriverInterface;
use Cycle\ORM\EntityManagerInterface;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\Select;
use Cycle\ORM\Select\SourceInterface;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use Yiisoft\Data\Reader\DataReaderInterface;

#[CoversClass(UserRepository::class)]
final class UserRepositoryTest extends Unit
{
    protected UnitTester $tester;

    private Select&MockObject $select;

    private EntityManagerInterface&MockObject $entityManager;

    private ORMInterface&MockObject $orm;

    private UserRepository $repository;

    public function testFindAllPreloaded(): void
    {
        $this->select
            ->expects($this->once())
            ->method('__call')
            ->willReturnCallback(
                function (string $method, array $args) {
                    if ($method === 'getDriver') {
                        $driverMock = $this
                            ->createMock(DriverInterface::class);
                        $driverMock
                            ->expects($this->once())
                            ->method('getType')
                            ->willReturn('SQLite');
                        return $driverMock;
                    }
                    return $this->select;
                },
            );

        $result = $this->repository->findAllPreloaded();

        $this->assertInstanceOf(DataReaderInterface::class, $result);
    }

    /**
     * @throws Exception
     */
    public function testFindUserWithExistingUser(): void
    {
        $userId = 1;
        $user = $this->createMock(User::class);

        $this->select
            ->expects($this->once())
            ->method('fetchOne')
            ->with(['id' => $userId])
            ->willReturn($user);

        $result = $this->repository->findUser($userId);

        $this->assertSame($user, $result);
    }

    public function testFindUserWithNonExistingUser(): void
    {
        $userId = 999;

        $this->select
            ->expects($this->once())
            ->method('fetchOne')
            ->with(['id' => $userId])
            ->willReturn(null);

        $result = $this->repository->findUser($userId);

        $this->assertNull($result);
    }

    /**
     * @throws Exception
     */
    public function testFindByLoginWithExistingUser(): void
    {
        $login = 'test@example.com';
        $user = $this->createMock(User::class);

        $this->select
            ->expects($this->once())
            ->method('fetchOne')
            ->with(['login' => $login])
            ->willReturn($user);

        $result = $this->repository->findByLogin($login);

        $this->assertSame($user, $result);
    }

    public function testFindByLoginWithNonExistingUser(): void
    {
        $login = 'nonexistent@example.com';

        $this->select
            ->expects($this->once())
            ->method('fetchOne')
            ->with(['login' => $login])
            ->willReturn(null);

        $result = $this->repository->findByLogin($login);

        $this->assertNull($result);
    }

    public function testGetUsersWithEmptyArray(): void
    {
        $this->select
            ->expects($this->once())
            ->method('__call')
            ->willReturnCallback(function ($name, $args) {
                if ($name === 'where') {
                    $this->assertIsArray($args[0]);
                    $this->assertArrayHasKey('id', $args[0]);
                    $this->assertArrayHasKey('in', $args[0]['id']);
                }
                return $this->select;
            });

        $this->select
            ->expects($this->once())
            ->method('fetchAll')
            ->willReturn([]);


        $result = $this->repository->getUsers([]);
        $this->assertEmpty($result);
    }

    /**
     * @throws Exception
     */
    public function testRemoveAll(): void
    {
        $source = $this->createMock(SourceInterface::class);
        $database = $this->createMock(DatabaseInterface::class);

        $this->orm
            ->expects($this->once())
            ->method('getSource')
            ->with(User::class)
            ->willReturn($source);

        $source
            ->expects($this->once())
            ->method('getDatabase')
            ->willReturn($database);

        $database
            ->expects($this->once())
            ->method('execute')
            ->with('DELETE FROM user');

        $this->repository->removeAll();
    }

    public function testSaveWithEmptyArray(): void
    {
        $this->entityManager
            ->expects($this->never())
            ->method('persist');

        $this->entityManager
            ->expects($this->never())
            ->method('run');

        $this->repository->save([]);
    }

    public function testDeleteWithEmptyArray(): void
    {
        $this->entityManager
            ->expects($this->never())
            ->method('delete');

        $this->entityManager
            ->expects($this->never())
            ->method('run');

        $this->repository->delete([]);
    }

    public function testSaveSingleUser(): void
    {
        $user = $this->createMock(User::class);

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($user);

        $this->entityManager
            ->expects($this->once())
            ->method('run');

        $this->repository->save([$user]);
    }

    public function testSaveMultipleUsers(): void
    {
        $user1 = $this->createMock(User::class);
        $user2 = $this->createMock(User::class);
        $user3 = $this->createMock(User::class);

        $expectedUsers = [$user1, $user2, $user3];
        $callCount = 0;

        $this->entityManager
            ->expects($this->exactly(3))
            ->method('persist')
            ->willreturnCallback(
                function (User $user) use ($expectedUsers, &$callCount) {
                    $this->assertSame(
                        $expectedUsers[$callCount],
                        $user,
                        "Unexpected user passed to persist() at call #" . ($callCount + 1),
                    );
                    $callCount++;

                    return $this->entityManager;
                },
            );

        $this->entityManager
            ->expects($this->once())
            ->method('run');

        $this->repository->save([$user1, $user2, $user3]);
    }

    public function testDeleteSingleUser(): void
    {
        $user = $this->createMock(User::class);

        $this->entityManager
            ->expects($this->once())
            ->method('delete')
            ->with($user);

        $this->entityManager
            ->expects($this->once())
            ->method('run');

        $this->repository->delete([$user]);
    }

    public function testDeleteMultipleUsers(): void
    {
        $user1 = $this->createMock(User::class);
        $user2 = $this->createMock(User::class);

        $expectedUsers = [$user1, $user2];
        $callCount = 0;

        $this->entityManager
            ->expects($this->exactly(2))
            ->method('delete')
            ->willreturnCallback(
                function (User $user) use ($expectedUsers, &$callCount) {
                    $this->assertSame(
                        $expectedUsers[$callCount],
                        $user,
                    );
                    $callCount++;

                    return $this->entityManager;
                },
            );

        $this->entityManager
            ->expects($this->once())
            ->method('run');

        $this->repository->delete([$user1, $user2]);
    }

    /**
     * @throws Exception
     */
    #[Override]
    protected function _before(): void
    {
        $this->select = $this->createMock(Select::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->orm = $this->createMock(ORMInterface::class);

        $this->repository = new UserRepository(
            $this->select,
            $this->entityManager,
            $this->orm,
        );
    }
}
