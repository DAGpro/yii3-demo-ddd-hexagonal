<?php

declare(strict_types=1);

namespace App\Tests\Unit\IdentityAccess\User\Infrastructure\Authentication;

use App\IdentityAccess\User\Domain\User;
use App\IdentityAccess\User\Infrastructure\Authentication\Identity;
use App\IdentityAccess\User\Infrastructure\Authentication\IdentityRepository;
use Cycle\ORM\EntityManager;
use Cycle\ORM\Select;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Throwable;

#[CoversClass(IdentityRepository::class)]
final class IdentityRepositoryTest extends TestCase
{
    private Select|MockObject $select;
    private EntityManager|MockObject $entityManager;
    private IdentityRepository $repository;

    public function testFindIdentityWithExistingId(): void
    {
        $identity = $this->createMock(Identity::class);

        $this->select
            ->expects($this->once())
            ->method('fetchOne')
            ->with(['id' => '123'])
            ->willReturn($identity);

        $result = $this->repository->findIdentity('123');

        $this->assertSame($identity, $result);
    }

    public function testFindIdentityWithNonExistingId(): void
    {
        $this->select
            ->expects($this->once())
            ->method('fetchOne')
            ->with(['id' => 'non-existing'])
            ->willReturn(null);

        $result = $this->repository->findIdentity('non-existing');

        $this->assertNull($result);
    }

    public function testFindByUserIdWithExistingUser(): void
    {
        $identity = $this->createMock(Identity::class);
        $userId = 1;

        $this->select
            ->expects($this->once())
            ->method('fetchOne')
            ->with(['user_id' => $userId])
            ->willReturn($identity);

        $result = $this->repository->findByUserId($userId);

        $this->assertSame($identity, $result);
    }

    public function testFindByUserIdWithNonExistingUser(): void
    {
        $userId = 999;

        $this->select
            ->expects($this->once())
            ->method('fetchOne')
            ->with(['user_id' => $userId])
            ->willReturn(null);

        $result = $this->repository->findByUserId($userId);

        $this->assertNull($result);
    }

    public function testSaveIdentity(): void
    {
        $identity = $this->createMock(Identity::class);

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($identity);

        $this->entityManager
            ->expects($this->once())
            ->method('run');

        $this->repository->save($identity);
    }

    public function testFindOrCreateWithExistingIdentity(): void
    {
        $user = $this->createMock(User::class);
        $identity = $this->createMock(Identity::class);
        $userId = 1;

        $user
            ->method('getId')
            ->willReturn($userId);

        $this->select
            ->expects($this->once())
            ->method('fetchOne')
            ->with(['user_id' => $userId])
            ->willReturn($identity);

        $result = $this->repository->findOrCreate($user);

        $this->assertSame($identity, $result);
    }

    /**
     * @throws Throwable
     * @throws Exception
     */
    public function testFindOrCreateWithNewIdentity(): void
    {
        $user = $this->createMock(User::class);
        $userId = 1;
        $newIdentity = $this->createMock(Identity::class);

        $user
            ->method('getId')
            ->willReturn($userId);

        // First call - return null (identity doesn't exist)
        $this->select
            ->expects($this->exactly(2))
            ->method('fetchOne')
            ->with(['user_id' => $userId])
            ->willReturnOnConsecutiveCalls(
                null,
                $newIdentity,
            );

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Identity::class));

        $this->entityManager
            ->expects($this->once())
            ->method('run');

        $result = $this->repository->findOrCreate($user);

        $this->assertSame($newIdentity, $result);
    }

    public function testFindOrCreateWithNullUserId(): void
    {
        $user = $this->createMock(User::class);

        $user
            ->method('getId')
            ->willReturn(null);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('User id is null');

        $this->repository->findOrCreate($user);
    }

    public function testFindOrCreateWhenIdentityNotFoundAfterCreation(): void
    {
        $user = $this->createMock(User::class);
        $userId = 1;

        $user
            ->method('getId')
            ->willReturn($userId);

        $this->select
            ->expects($this->exactly(2))
            ->method('fetchOne')
            ->with(['user_id' => $userId])
            ->willReturn(null);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Identity not found');

        $this->repository->findOrCreate($user);
    }

    protected function setUp(): void
    {
        $this->select = $this->createMock(Select::class);
        $this->entityManager = $this->createMock(EntityManager::class);
        $this->repository = new IdentityRepository($this->select, $this->entityManager);
    }
}
