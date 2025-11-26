<?php

declare(strict_types=1);

namespace App\IdentityAccess\User\Slice\User;

use App\IdentityAccess\User\Domain\Exception\IdentityException;

interface UserServiceInterface
{
    public function createUser(string $login, string $password): void;

    /**
     * @throws IdentityException
     */
    public function deleteUser(int $userId): void;

    public function removeAll(): void;
}
