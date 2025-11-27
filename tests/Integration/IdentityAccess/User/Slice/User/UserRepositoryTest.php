<?php

declare(strict_types=1);

namespace App\Tests\Integration\IdentityAccess\User\Slice\User;

use App\IdentityAccess\User\Domain\Port\UserRepositoryInterface;
use App\IdentityAccess\User\Domain\User;
use App\Tests\Integration\TestCase;
use App\Tests\UnitTester;
use Override;

class UserRepositoryTest extends TestCase
{
    protected UnitTester $tester;

    private UserRepositoryInterface $repository;

    public function testFindUser(): void
    {
        $user = new User('testuser', 'hashed_password');
        $this->repository->save([$user]);
        $userId = $user->getId();

        $foundUser = $this->repository->findUser($userId);

        $this->assertNotNull($foundUser);
        $this->assertEquals('testuser', $foundUser->getLogin());
        $this->assertTrue($foundUser->validatePassword('hashed_password'));
    }

    public function testFindByLogin(): void
    {
        $user = new User('testuser', 'hashed_password');
        $this->repository->save([$user]);

        $foundUser = $this->repository->findByLogin('testuser');

        $this->assertNotNull($foundUser);
        $this->assertTrue($foundUser->validatePassword('hashed_password'));
    }

    public function testGetUsers(): void
    {
        $user1 = new User('user1', 'pass1');
        $user2 = new User('user2', 'pass2');
        $this->repository->save([$user1, $user2]);

        $userIds = [$user1->getId(), $user2->getId()];

        $users = $this->repository->getUsers($userIds);

        $this->assertCount(2, $users);
        $logins = array_map(fn($u) => $u->getLogin(), iterator_to_array($users));
        $this->assertContains('user1', $logins);
        $this->assertContains('user2', $logins);
    }

    public function testRemoveAll(): void
    {
        $user = new User('testuser', 'hashed_password');
        $this->repository->save([$user]);

        $this->repository->removeAll();

        $foundUser = $this->repository->findByLogin('testuser');
        $this->assertNull($foundUser);
    }

    public function testSaveAndDelete(): void
    {
        $user = new User('testuser', 'hashed_password');
        $this->repository->save([$user]);
        $userId = $user->getId();

        $foundUser = $this->repository->findUser($userId);
        $this->assertNotNull($foundUser);

        $this->repository->delete([$user]);

        $deletedUser = $this->repository->findUser($userId);
        $this->assertNull($deletedUser);
    }

    public function testFindAllPreloaded(): void
    {
        $user1 = new User('user1', 'pass1');
        $user2 = new User('user2', 'pass2');
        $this->repository->save([$user1, $user2]);

        $dataReader = $this->repository->findAllPreloaded();
        $users = $dataReader->read();

        $this->assertGreaterThanOrEqual(2, count($users));
        $logins = array_map(static fn($u) => $u->getLogin(), $users);
        $this->assertContains('user1', $logins);
        $this->assertContains('user2', $logins);
    }

    #[Override]
    protected function _before(): void
    {
        parent::_before();

        if (self::$container === null) {
            $this->initializeContainer();
        }

        /** @var UserRepositoryInterface $repository */
        $repository = self::$orm->getRepository(User::class);
        self::$database = $repository
            ->select()
            ->getBuilder()
            ->getLoader()
            ->getSource()
            ->getDatabase();

        $this->repository = $repository;

        $this->beginTransaction();
    }
}
