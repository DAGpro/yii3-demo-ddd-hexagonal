<?php

declare(strict_types=1);

namespace App\IdentityAccess\User\Domain\Port;

use App\IdentityAccess\User\Domain\User;
use Cycle\ORM\Select;

interface UserRepositoryInterface
{
    public function select(): Select;

    public function findUser(int $userId): ?User;

    public function findByLogin(string $login): ?User;

    /**
     * @param array<int, int> $userIds
     * @return iterable<User>
     */
    public function getUsers(array $userIds): iterable;

    public function removeAll(): void;

    /**
     * @param array<int, User> $users
     */
    public function save(array $users): void;

    /**
     * @param array<int, User> $users
     */
    public function delete(array $users): void;

}
