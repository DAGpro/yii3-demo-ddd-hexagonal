<?php

declare(strict_types=1);

namespace App\IdentityAccess\User\Infrastructure\Persistence;

use App\IdentityAccess\User\Domain\Port\UserRepositoryInterface;
use App\IdentityAccess\User\Domain\User;
use Cycle\ORM\EntityManager;
use Cycle\ORM\ORMInterface;
use Cycle\Database\Injection\Parameter;
use Cycle\ORM\Select;
use Cycle\ORM\Select\Repository;
use Throwable;

final class UserRepository extends Repository implements UserRepositoryInterface
{
    private EntityManager $entityManager;
    private ORMInterface $orm;

    public function __construct(Select $select, ORMInterface $orm)
    {
        $this->entityManager = new EntityManager($orm);
        $this->orm = $orm;
        parent::__construct($select);
    }

    public function findUser(int $userId): ?User
    {
        return $this->findOne(['id' => $userId]);
    }

    public function findByLogin(string $login): ?User
    {
        return $this->findOne(['login' => $login]);
    }

    public function getUsers(array $userIds): iterable
    {
        return $this->select()
            ->where([
                'id' => [
                    'in' => new Parameter($userIds),
                ]
            ])
            ->fetchAll();
    }

    public function removeAll(): void
    {
        $source = $this->orm->getSource(User::class);
        $db = $source->getDatabase();
        $db->execute('DELETE FROM users');
    }

    /**
     * @throws Throwable
     */
    public function save(array $users): void
    {
        foreach ($users as $entity) {
            $this->entityManager->persist($entity);
        }
        $this->entityManager->run();
    }

    public function delete(array $users): void
    {
        foreach ($users as $entity) {
            $this->entityManager->delete($entity);
        }
        $this->entityManager->run();
    }

}
