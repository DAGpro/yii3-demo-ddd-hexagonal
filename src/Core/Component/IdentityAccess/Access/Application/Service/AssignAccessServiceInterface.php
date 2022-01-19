<?php

namespace App\Core\Component\IdentityAccess\Access\Application\Service;

use App\Core\Component\IdentityAccess\Access\Domain\Exception\AssignedItemException;

interface AssignAccessServiceInterface
{
    /**
     * @param RoleDTO $roleDTO
     * @param string $userId
     * @throws AssignedItemException
     */
    public function assignRole(RoleDTO $roleDTO, string $userId): void;

    /**
     * @param PermissionDTO $permissionDTO
     * @param string $userId
     * @throws AssignedItemException
     */
    public function assignPermission(PermissionDTO $permissionDTO, string $userId): void;

    public function revokeRole(RoleDTO $roleDTO, string $userId): void;

    public function revokePermission(PermissionDTO $permissionDTO, string $userId): void;

    public function revokeAll(string $userId): void;

    public function clearAssignments(): void;
}
