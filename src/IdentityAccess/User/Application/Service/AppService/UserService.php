<?php

declare(strict_types=1);

namespace App\IdentityAccess\User\Application\Service\AppService;

use App\IdentityAccess\User\Application\Service\UserServiceInterface;
use App\IdentityAccess\User\Domain\Exception\IdentityException;
use App\IdentityAccess\User\Domain\Port\UserRepositoryInterface;
use App\IdentityAccess\User\Domain\User;

final class UserService implements UserServiceInterface
{
    private UserRepositoryInterface $repository;

    public function __construct(
        UserRepositoryInterface $repository,
    ) {
        $this->repository = $repository;
    }

    /**
     * @throws IdentityException
     * @throws \Throwable
     */
    public function createUser(string $login, string $password): void
    {
        if (null !== $this->repository->findByLogin($login)) {
            throw new IdentityException('This user already exists!');
        }

        $user = new User($login, $password);

        $this->repository->save([$user]);
    }

    /**
     * @throws IdentityException
     */
    public function deleteUser(int $userId): void
    {
        if (!($user = $this->repository->findUser($userId))) {
            throw new IdentityException('This user does not exist!');
        }

        $this->repository->delete([$user]);
    }

    public function removeAll(): void
    {
        $this->repository->removeAll();
    }
}
