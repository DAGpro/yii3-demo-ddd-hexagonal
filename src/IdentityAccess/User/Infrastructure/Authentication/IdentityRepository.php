<?php

declare(strict_types=1);

namespace App\IdentityAccess\User\Infrastructure\Authentication;

use App\IdentityAccess\User\Domain\User;
use Cycle\ORM\EntityManager;
use Cycle\ORM\Select;
use Cycle\ORM\Select\Repository;
use Override;
use RuntimeException;
use Throwable;


/**
 * @template-extends Repository<Identity>
 */
final class IdentityRepository extends Repository implements IdentityRepositoryInterface
{
    /**
     * @param Select<Identity> $select
     */
    public function __construct(
        Select $select,
        private readonly EntityManager $entityManager,
    ) {
        parent::__construct($select);
    }

    /**
     * @throws Throwable
     */
    #[Override]
    public function findOrCreate(User $user): Identity
    {
        $id = $user->getId();
        if ($id === null) {
            throw new RuntimeException('User id is null');
        }
        $identity = $this->findByUserId($id);
        if ($identity === null) {
            $this->save(new Identity($user));
            $identity = $this->findByUserId($id);
            if ($identity === null) {
                throw new RuntimeException('Identity not found');
            }
        }
        return $identity;
    }


    #[Override]
    public function findIdentity(string $id): ?Identity
    {
        $result = $this->findOne(['id' => $id]);
        return $result instanceof Identity ? $result : null;
    }

    /**
     * @psalm-suppress LessSpecificReturnStatement
     * @psalm-suppress MoreSpecificReturnType
     */
    #[Override]
    public function findByUserId(int $userId): ?Identity
    {
        $result = $this->findOne(['user_id' => $userId]);
        return $result instanceof Identity ? $result : null;
    }

    /**
     * @throws Throwable
     */
    #[Override]
    public function save(Identity $identity): void
    {
        $this->entityManager->persist($identity);
        $this->entityManager->run();
    }
}
