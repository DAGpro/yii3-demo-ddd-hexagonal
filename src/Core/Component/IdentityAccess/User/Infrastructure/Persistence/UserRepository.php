<?php

declare(strict_types=1);

namespace App\Core\Component\IdentityAccess\User\Infrastructure\Persistence;

use App\Core\Component\IdentityAccess\User\Domain\Port\UserRepositoryInterface;
use App\Core\Component\IdentityAccess\User\Domain\User;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\Select;
use Cycle\ORM\Transaction;
use Cycle\ORM\TransactionInterface;
use Spiral\Database\Injection\Parameter;
use Throwable;

final class UserRepository extends Select\Repository implements UserRepositoryInterface
{
    private TransactionInterface $transaction;
    private ORMInterface $orm;

    public function __construct(Select $select, ORMInterface $orm)
    {
        $this->transaction = new Transaction($orm);
        $this->orm = $orm;
        parent::__construct($select);
    }

    public function findUser(int $userId): ?User
    {
        return $this->findOne(['id' => $userId]);
    }

    public function findByLogin(string $login): ?User
    {
        return $this->findBy('login', $login);
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
            $this->transaction->persist($entity);
        }
        $this->transaction->run();
    }

    public function delete(array $users): void
    {
        foreach ($users as $entity) {
            $this->transaction->delete($entity);
        }
        $this->transaction->run();
    }

    private function findBy(string $field, string $value): ?User
    {
        return $this->findOne([$field => $value]);
    }
}
