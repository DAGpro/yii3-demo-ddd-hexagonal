<?php

declare(strict_types=1);

namespace App\IdentityAccess\Access\Application\Service;

interface AccessRightsServiceInterface
{
    public function existRole(string $roleName): bool;

    public function getRoleByName(string $roleName): ?RoleDTO;

    /**
     * @return array<string, RoleDTO>
     */
    public function getRoles(): array;

    public function existPermission(string $permissionName): bool;

    public function getPermissionByName(string $permissionName): ?PermissionDTO;

    /**
     * @return array<string, PermissionDTO>
     */
    public function getPermissions(): array;

    public function getChildRoles(RoleDTO $roleDTO): array;

    /**
     * @return array<string, RoleDTO>
     */
    public function getNestedRoles(RoleDTO $roleDTO): array;

    /**
     * @return array<string, PermissionDTO>
     */
    public function getPermissionsByRole(RoleDTO $roleDTO): array;

    public function getNestedPermissionsByRole(RoleDTO $roleDTO): array;

    public function hasChildren(RoleDTO $parentDTO): bool;

    public function setDefaultRoles(array $roles): AccessRightsServiceInterface;

    public function getDefaultRoleNames(): array;

    public function getDefaultRoles(): array;
}
