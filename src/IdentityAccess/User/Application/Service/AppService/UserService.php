<?php

declare(strict_types=1);

namespace App\IdentityAccess\User\Application\Service\AppService;

use App\IdentityAccess\User\Application\Service\UserServiceInterface;
use App\IdentityAccess\User\Domain\Exception\IdentityException;
use App\IdentityAccess\User\Domain\Port\UserRepositoryInterface;
use App\IdentityAccess\User\Domain\User;
use Throwable;

final readonly class UserService implements UserServiceInterface
{
    public function __construct(
        private UserRepositoryInterface $repository,
    ) {}

    /**
     * @throws IdentityException
     * @throws Throwable
     */
    #[\Override]
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
    #[\Override]
    public function deleteUser(int $userId): void
    {
        if (!($user = $this->repository->findUser($userId))) {
            throw new IdentityException('This user does not exist!');
        }

        $this->repository->delete([$user]);
    }

    #[\Override]
    public function removeAll(): void
    {
        $this->repository->removeAll();
    }
}
