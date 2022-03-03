<?php

declare(strict_types=1);

namespace App\IdentityAccess\Access\Application\Service;

interface AccessManagementServiceInterface
{
    public function addRole(RoleDTO $roleDTO): void;

    public function removeRole(RoleDTO $roleDTO): void;

    public function addPermission(PermissionDTO $permissionDTO): void;

    public function removePermission(PermissionDTO $permissionDTO): void;

    public function addChildRole(RoleDTO $parentDTO, RoleDTO $childDTO): void;

    public function addChildPermission(RoleDTO $parentDTO, PermissionDTO $childDTO): void;

    public function removeChildRole(RoleDTO $parentDTO, RoleDTO $childDTO): void;

    public function removeChildPermission(RoleDTO $parentRoleDTO, PermissionDTO $childPermissionDTO): void;

    public function removeChildren(RoleDTO $parentDTO): void;

    public function hasChildRole(RoleDTO $parentRoleDTO, RoleDTO $childRoleDTO): bool;

    public function hasChildPermission(RoleDTO $parentRoleDTO, PermissionDTO $childPermissionDTO): bool;

    public function clearAccessRights(): void;
}
