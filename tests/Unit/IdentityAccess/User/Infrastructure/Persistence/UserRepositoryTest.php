<?php

declare(strict_types=1);

namespace App\Tests\Unit\IdentityAccess\User\Infrastructure\Persistence;

use App\IdentityAccess\User\Domain\User;
use App\IdentityAccess\User\Infrastructure\Persistence\UserRepository;
use Cycle\Database\DatabaseInterface;
use Cycle\ORM\EntityManagerInterface;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\Select;
use Cycle\ORM\Select\SourceInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[CoversClass(UserRepository::class)]
final class UserRepositoryTest extends TestCase
{
    private Select|MockObject $select;
    private EntityManagerInterface|MockObject $entityManager;
    private ORMInterface|MockObject $orm;
    private UserRepository $repository;

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
        // Создаем реальный объект Select, чтобы избежать проблем с моками
        $reflection = new ReflectionClass($this->repository);
        $selectProperty = $reflection->getProperty('select');
        $selectProperty->setAccessible(true);

        // Сохраняем оригинальный select
        $originalSelect = $selectProperty->getValue($this->repository);

        try {
            // Создаем заглушку, которая вернет пустой массив
            $emptySelect = $this->createMock(Select::class);
            $emptySelect
                ->method('__call')
                ->willReturnCallback(function ($name, $args) {
                    if ($name === 'fetchAll') {
                        return [];
                    }
                    return $this->createMock(Select::class);
                });

            $selectProperty->setValue($this->repository, $emptySelect);

            // Тестируем метод с пустым массивом
            $result = $this->repository->getUsers([]);
            $this->assertIsIterable($result);
            $this->assertEmpty(iterator_to_array($result));
        } finally {
            // Восстанавливаем оригинальный select
            $selectProperty->setValue($this->repository, $originalSelect);
        }
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
            ->with('DELETE FROM users');

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

    /**
     * @throws Exception
     */
    protected function setUp(): void
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
