<?php

declare(strict_types=1);

namespace App\Core\Component\IdentityAccess\User\Application\Service\AppService;

use App\Core\Component\IdentityAccess\User\Application\Service\UserServiceInterface;
use App\Core\Component\IdentityAccess\User\Domain\Exception\IdentityException;
use App\Core\Component\IdentityAccess\User\Domain\User;
use App\Core\Component\IdentityAccess\User\Infrastructure\Persistence\UserRepository;
use Yiisoft\Access\AccessCheckerInterface;
use Yiisoft\User\CurrentUser;

final class UserService implements UserServiceInterface
{
    private UserRepository $repository;

    public function __construct(
        CurrentUser $currentUser,
        UserRepository $repository,
        AccessCheckerInterface $accessChecker
    ) {
        $this->currentUser = $currentUser;
        $this->repository = $repository;
        $this->accessChecker = $accessChecker;
    }

    public function createUser(string $login, string $password): void
    {
        if (null !== $this->repository->findByLogin($login)) {
            throw new \Exception('This user already exists!');
        }

        $user = new User($login, $password);

        $this->repository->save([$user]);
    }

    /**
     * @param string $userId
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
