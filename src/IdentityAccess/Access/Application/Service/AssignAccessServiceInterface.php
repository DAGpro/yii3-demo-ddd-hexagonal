<?php

declare(strict_types=1);

namespace App\IdentityAccess\Access\Application\Service;

use App\IdentityAccess\Access\Domain\Exception\AssignedItemException;
use App\IdentityAccess\Access\Domain\Exception\NotExistItemException;

interface AssignAccessServiceInterface
{
    /**
     * @throws AssignedItemException
     */
    public function assignRole(RoleDTO $roleDTO, string|int $userId): void;

    /**
     * @throws AssignedItemException
     * @throws NotExistItemException
     */
    public function assignPermission(PermissionDTO $permissionDTO, string|int $userId): void;

    /**
     * @throws AssignedItemException
     */
    public function revokeRole(RoleDTO $roleDTO, string|int $userId): void;

    /**
     * @throws AssignedItemException
     */
    public function revokePermission(PermissionDTO $permissionDTO, string|int $userId): void;

    public function revokeAll(string|int $userId): void;

    public function clearAssignments(): void;
}
