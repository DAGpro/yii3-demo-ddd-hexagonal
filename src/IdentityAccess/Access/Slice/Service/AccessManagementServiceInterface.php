<?php

declare(strict_types=1);

namespace App\IdentityAccess\Access\Slice\Service;

use App\IdentityAccess\Access\Domain\Exception\AssignedItemException;
use App\IdentityAccess\Access\Domain\Exception\ExistItemException;
use App\IdentityAccess\Access\Domain\Exception\NotExistItemException;

interface AccessManagementServiceInterface
{
    /**
     * @throws ExistItemException
     */
    public function addRole(RoleDTO $roleDTO): void;

    /**
     * @throws AssignedItemException
     * @throws NotExistItemException
     */
    public function removeRole(RoleDTO $roleDTO): void;

    /**
     * @throws ExistItemException
     */
    public function addPermission(PermissionDTO $permissionDTO): void;

    /**
     * @throws AssignedItemException
     * @throws NotExistItemException
     */
    public function removePermission(PermissionDTO $permissionDTO): void;

    /**
     * @throws ExistItemException
     * @throws NotExistItemException
     */
    public function addChildRole(RoleDTO $parentDTO, RoleDTO $childDTO): void;

    /**
     * @throws ExistItemException
     * @throws NotExistItemException
     */
    public function addChildPermission(RoleDTO $parentDTO, PermissionDTO $childDTO): void;

    /**
     * @throws NotExistItemException
     */
    public function removeChildRole(RoleDTO $parentDTO, RoleDTO $childDTO): void;

    /**
     * @throws NotExistItemException
     */
    public function removeChildPermission(RoleDTO $parentRoleDTO, PermissionDTO $childPermissionDTO): void;

    /**
     * @throws NotExistItemException
     */
    public function removeChildren(RoleDTO $parentDTO): void;

    public function hasChildRole(RoleDTO $parentRoleDTO, RoleDTO $childRoleDTO): bool;

    public function hasChildPermission(RoleDTO $parentRoleDTO, PermissionDTO $childPermissionDTO): bool;

    public function clearAccessRights(): void;
}
