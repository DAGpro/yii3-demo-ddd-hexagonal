<?php

namespace App\Core\Component\IdentityAccess\Access\Application\Service;

interface AccessRightsServiceInterface
{
    public function existRole(string $roleName): bool;

    public function getRoleByName(string $roleName): ?RoleDTO;

    public function getRoles(): array;

    public function existPermission(string $permissionName): bool;

    public function getPermissionByName(string $permissionName): ?PermissionDTO;

    public function getPermissions(): array;

    public function getChildRoles(RoleDTO $roleDTO): array;

    public function getNestedRoles(RoleDTO $roleDTO): array;

    public function getPermissionsByRole(RoleDTO $roleDTO): array;

    public function getNestedPermissionsByRole(RoleDTO $roleDTO): array;

    public function hasChildren(RoleDTO $parentDTO): bool;

    public function setDefaultRoles(array $roles): AccessRightsServiceInterface;

    public function getDefaultRoles(): array;

    public function getDefaultRoleInstances(): array;
}
