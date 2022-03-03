<?php

namespace App\IdentityAccess\User\Application\Service;


interface UserServiceInterface
{
    public function createUser(string $login, string $password): void;

    public function deleteUser(int $userId): void;

    public function removeAll(): void;
}
