<?php

declare(strict_types=1);

namespace App\Infrastructure\Authentication;

use App\Core\Component\IdentityAccess\User\Domain\User;
use Cycle\ORM\Select;
use Throwable;
use Cycle\ORM\EntityManager;
use Yiisoft\Auth\IdentityRepositoryInterface;

final class IdentityRepository extends Select\Repository implements IdentityRepositoryInterface
{
    private EntityManager $entityManager;

    public function __construct(Select $select, EntityManager $entityWriter)
    {
        $this->entityManager = $entityWriter;
        parent::__construct($select);
    }

    public function findOrCreate(User $user): Identity
    {
        $identity = $this->findByUserId($user->getId());
        if ($identity === null) {
            $this->save(new Identity($user));
            $identity = $this->findByUserId($user->getId());
        }
        return $identity;
    }

    /**
     * @param string $id
     *
     * @return Identity|null
     */
    public function findIdentity(string $id): ?Identity
    {
        return $this->findOne(['id' => $id]);
    }

    public function findByUserId(int $userId): ?Identity
    {
        return $this->findOne(['user_id' => $userId]);
    }

    /**
     * @throws Throwable
     */
    public function save(Identity $identity): void
    {
        $this->entityManager->persist($identity);
        $this->entityManager->run();
    }
}
