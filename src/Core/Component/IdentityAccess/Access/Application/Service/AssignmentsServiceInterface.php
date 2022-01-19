<?php

namespace App\Core\Component\IdentityAccess\Access\Application\Service;

use App\Core\Component\IdentityAccess\User\Domain\User;

interface AssignmentsServiceInterface
{
    public function getUserIdsByRole(RoleDTO $roleDTO): array;

    public function getRolesByUser(string $userId): array;

    public function getPermissionsByUser(string $userId): array;

    public function userHasPermission(string $userId, string $permissionName): bool;

    public function userHasRole(string $userId, string $roleName): bool;

    public function isAssignedRoleToUsers(RoleDTO $roleDTO): bool;

    public function isAssignedPermissionToUsers(PermissionDTO $permissionDTO): bool;

    public function userAssignmentExist(string $userId, string $roleName): bool;

    public function assignmentExist(string $itemName): bool;

    public function getUserAssignments(User $user): UserAssignmentsDTO;

    public function getAssignments(): array;
}
