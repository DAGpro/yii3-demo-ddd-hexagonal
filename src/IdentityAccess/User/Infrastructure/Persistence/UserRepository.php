<?php

declare(strict_types=1);

namespace App\IdentityAccess\User\Infrastructure\Persistence;

use App\IdentityAccess\User\Domain\Port\UserRepositoryInterface;
use App\IdentityAccess\User\Domain\User;
use Cycle\Database\Injection\Parameter;
use Cycle\ORM\EntityManagerInterface;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\Select;
use Cycle\ORM\Select\Repository;
use Override;
use Throwable;
use Yiisoft\Data\Cycle\Reader\EntityReader;
use Yiisoft\Data\Reader\DataReaderInterface;

/**
 * @extends Repository<User>
 */
final class UserRepository extends Repository implements UserRepositoryInterface
{
    /**
     * @param Select<User> $select
     */
    public function __construct(
        protected Select $select,
        private readonly EntityManagerInterface $entityManager,
        private readonly ORMInterface $orm,
    ) {
        parent::__construct($select);
    }

    #[Override]
    public function findAllPreloaded(): DataReaderInterface
    {
        $select = $this->select();

        return new EntityReader($select);
    }

    #[Override]
    public function findUser(int $userId): ?User
    {
        $result = $this->findOne(['id' => $userId]);
        return $result instanceof User ? $result : null;
    }

    #[Override]
    public function findByLogin(string $login): ?User
    {
        $result = $this->findOne(['login' => $login]);
        return $result instanceof User ? $result : null;
    }

    #[Override]
    public function getUsers(array $userIds): iterable
    {
        return $this
            ->select()
            ->where([
                'id' => [
                    'in' => new Parameter($userIds),
                ],
            ])
            ->fetchAll();
    }

    #[Override]
    public function removeAll(): void
    {
        $source = $this->orm->getSource(User::class);
        $db = $source->getDatabase();
        $db->execute('DELETE FROM user');
    }

    /**
     * @throws Throwable
     */
    #[Override]
    public function save(array $users): void
    {
        if (empty($users)) {
            return;
        }

        foreach ($users as $entity) {
            $this->entityManager->persist($entity);
        }
        $this->entityManager->run();
    }

    #[Override]
    public function delete(array $users): void
    {
        if (empty($users)) {
            return;
        }

        foreach ($users as $entity) {
            $this->entityManager->delete($entity);
        }
        $this->entityManager->run();
    }

}
