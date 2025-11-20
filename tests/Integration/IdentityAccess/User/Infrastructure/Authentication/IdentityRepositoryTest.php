<?php

declare(strict_types=1);

namespace App\Tests\Integration\IdentityAccess\User\Infrastructure\Authentication;

use App\IdentityAccess\User\Domain\User;
use App\IdentityAccess\User\Infrastructure\Authentication\Identity;
use App\IdentityAccess\User\Infrastructure\Authentication\IdentityRepositoryInterface;
use App\Tests\Integration\TestCase;
use App\Tests\UnitTester;
use Override;
use Throwable;

class IdentityRepositoryTest extends TestCase
{
    protected UnitTester $tester;

    private IdentityRepositoryInterface $repository;

    /**
     * @throws Throwable
     */
    public function testFindOrCreate(): void
    {
        $user = new User('testuser', 'password');
        self::$orm->getRepository(User::class)->save([$user]);

        $identity = $this->repository->findOrCreate($user);

        $this->assertNotNull($identity);
        $this->assertEquals($user->getId(), $identity->getUser()->getId());

        $existingIdentity = $this->repository->findOrCreate($user);
        $this->assertEquals($identity->getId(), $existingIdentity->getId());
    }

    /**
     * @throws Throwable
     */
    public function testFindIdentity(): void
    {
        $user = new User('testuser', 'password');
        self::$orm->getRepository(User::class)->save([$user]);

        $identity = new Identity($user);
        $this->repository->save([$identity]);
        $identityId = $identity->getId();

        $foundIdentity = $this->repository->findIdentity($identityId);

        $this->assertNotNull($foundIdentity);
        $this->assertEquals($identityId, $foundIdentity->getId());
        $this->assertEquals($user->getId(), $foundIdentity->getUser()->getId());
    }

    /**
     * @throws Throwable
     */
    public function testFindByUserId(): void
    {
        $user = new User('testuser', 'password');
        self::$orm->getRepository(User::class)->save([$user]);

        $identity = new Identity($user);
        $this->repository->save([$identity]);
        $userId = $user->getId();

        $foundIdentity = $this->repository->findByUserId($userId);

        $this->assertNotNull($foundIdentity);
        $this->assertEquals($userId, $foundIdentity->getUser()->getId());
    }

    public function testSave(): void
    {
        $user = new User('testuser', 'password');
        self::$orm->getRepository(User::class)->save([$user]);

        $identity = new Identity($user);
        $this->repository->save([$identity]);

        $foundIdentity = $this->repository->findByUserId($user->getId());
        $this->assertNotNull($foundIdentity);
        $this->assertEquals($user->getId(), $foundIdentity->getUser()->getId());
    }

    /**
     * @throws Throwable
     */
    public function testDelete(): void
    {
        $user = new User('testuser', 'password');
        self::$orm->getRepository(User::class)->save([$user]);

        $identity = new Identity($user);
        $this->repository->save([$identity]);
        $identityId = $identity->getId();

        $foundIdentity = $this->repository->findIdentity($identityId);
        $this->assertNotNull($foundIdentity);

        $this->repository->delete([$identity]);

        $deletedIdentity = $this->repository->findIdentity($identityId);
        $this->assertNull($deletedIdentity);
    }

    /**
     * @throws Throwable
     */
    public function testDeleteMultipleIdentities(): void
    {
        $user1 = new User('user1', 'password1');
        $user2 = new User('user2', 'password2');
        self::$orm->getRepository(User::class)->save([$user1, $user2]);

        $identity1 = new Identity($user1);
        $identity2 = new Identity($user2);
        $this->repository->save([$identity1, $identity2]);

        $identity1Id = $identity1->getId();
        $identity2Id = $identity2->getId();

        $this->assertNotNull($this->repository->findIdentity($identity1Id));
        $this->assertNotNull($this->repository->findIdentity($identity2Id));

        $this->repository->delete([$identity1, $identity2]);

        $this->assertNull($this->repository->findIdentity($identity1Id));
        $this->assertNull($this->repository->findIdentity($identity2Id));
    }

    #[Override]
    protected function _before(): void
    {
        parent::_before();

        if (self::$container === null) {
            $this->initializeContainer();
        }

        /** @var IdentityRepositoryInterface $repository */
        $repository = self::$orm->getRepository(Identity::class);
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
