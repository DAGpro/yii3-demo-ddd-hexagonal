<?php

declare(strict_types=1);

namespace App\IdentityAccess\User\Infrastructure\Authentication;


use App\IdentityAccess\User\Domain\User;
use Throwable;

interface IdentityRepositoryInterface extends \Yiisoft\Auth\IdentityRepositoryInterface
{
    /**
     * @throws Throwable
     */
    public function findOrCreate(User $user): Identity;

    public function findByUserId(int $userId): ?Identity;

    /**
     * @param array<Identity> $identities
     * @throws Throwable
     */
    public function save(array $identities): void;

    /**
     * @param array<Identity> $identities
     */
    public function delete(array $identities): void;
}
