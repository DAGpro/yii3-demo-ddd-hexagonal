<?php

declare(strict_types=1);

namespace App\IdentityAccess\User\Domain\Port;

use App\IdentityAccess\User\Domain\User;

interface UserRepositoryInterface
{
    public function findUser(int $userId): ?User;

    public function findByLogin(string $login): ?User;

    public function getUsers(array $userIds): iterable;

    public function removeAll(): void;

    public function save(array $users): void;

    public function delete(array $users): void;

}
