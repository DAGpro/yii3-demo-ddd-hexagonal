<?php

declare(strict_types=1);

namespace App\IdentityAccess\User\Application\Service\AppService;

use App\IdentityAccess\User\Application\Service\UserQueryServiceInterface;
use App\IdentityAccess\User\Domain\Port\UserRepositoryInterface;
use App\IdentityAccess\User\Domain\User;
use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Yii\Cycle\Data\Reader\EntityReader;

final class UserQueryService implements UserQueryServiceInterface
{
    private UserRepositoryInterface $repository;

    public function __construct(UserRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function findAllPreloaded(array $scope = [], array $orderBy = []): DataReaderInterface
    {
        return new EntityReader($this->repository->select()->where($scope)->orderBy($orderBy));
    }

    public function getUser(int $userId): ?User
    {
        return $this->repository->findUser($userId);
    }

    public function findByLogin(string $login): ?User
    {
        return  $this->repository->findByLogin($login);
    }

    public function getUsers(array $userIds): iterable
    {
        return $this->repository->getUsers($userIds);
    }
}
