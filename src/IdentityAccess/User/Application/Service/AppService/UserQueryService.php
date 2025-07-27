<?php

declare(strict_types=1);

namespace App\IdentityAccess\User\Application\Service\AppService;

use App\IdentityAccess\User\Application\Service\UserQueryServiceInterface;
use App\IdentityAccess\User\Domain\Port\UserRepositoryInterface;
use App\IdentityAccess\User\Domain\User;
use Override;
use Yiisoft\Data\Cycle\Reader\EntityReader;
use Yiisoft\Data\Reader\DataReaderInterface;

final readonly class UserQueryService implements UserQueryServiceInterface
{
    public function __construct(
        private UserRepositoryInterface $repository,
    ) {
    }

    #[Override]
    public function findAllPreloaded(array $scope = [], array $orderBy = []): DataReaderInterface
    {
        $select = $this->repository
            ->select()
            ->where($scope)
            ->orderBy($orderBy);

        return new EntityReader($select);
    }

    #[Override]
    public function getUser(int $userId): ?User
    {
        return $this->repository->findUser($userId);
    }

    #[Override]
    public function findByLogin(string $login): ?User
    {
        return $this->repository->findByLogin($login);
    }

    /**
     * @param array<int, int> $userIds
     * @return iterable<User>
     */
    #[Override]
    public function getUsers(array $userIds): iterable
    {
        return $this->repository->getUsers($userIds);
    }
}
