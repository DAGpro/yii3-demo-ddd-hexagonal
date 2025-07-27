<?php

declare(strict_types=1);

namespace App\IdentityAccess\User\Application\Service;

use App\IdentityAccess\User\Domain\User;
use Yiisoft\Data\Reader\DataReaderInterface;

interface UserQueryServiceInterface
{
    public function findAllPreloaded(array $scope = [], array $orderBy = []): DataReaderInterface;

    public function getUser(int $userId): ?User;

    public function findByLogin(string $login): ?User;

    /**
     * @param array<int, int> $userIds
     * @return iterable<User>
     */
    public function getUsers(array $userIds): iterable;
}
