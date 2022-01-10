<?php

namespace App\Core\Component\IdentityAccess\User\Application\Service;

use App\Core\Component\IdentityAccess\User\Domain\User;
use Yiisoft\Data\Reader\DataReaderInterface;

interface UserQueryServiceInterface
{
    public function findAllPreloaded(): DataReaderInterface;

    public function getUser(int $userId): ?User;

    public function findByLogin(string $login): ?User;

    public function getUsers(array $userIds): iterable;
}
