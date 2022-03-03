<?php

declare(strict_types=1);

namespace App\Infrastructure\Authorization;

use App\IdentityAccess\Access\Application\Service\AssignmentsServiceInterface;
use App\IdentityAccess\Access\Application\Service\RoleDTO;

final class AuthorizationService
{
    private AssignmentsServiceInterface $assignmentsService;

    public function __construct(AssignmentsServiceInterface $assignmentsService)

    {
        $this->assignmentsService = $assignmentsService;
    }

    public function userHasPermission(string|int $userId, string $permission): bool
    {
        return $this->assignmentsService->userHasPermission($userId, $permission);
    }

    public function userHasRole(string|int $userId, string $roleName): bool
    {
        if ($this->assignmentsService->userHasRole($userId, $roleName)) {
            return true;
        }

        return $this->userHasAccessThroughChildRole($userId, $roleName);
    }

    private function userHasAccessThroughChildRole(string|int $userId, string $roleName): bool
    {
        $userRoles = $this->assignmentsService->getRolesByUser($userId);

        $childRoles = [];
        /** @var RoleDTO $role */
        foreach ($userRoles as $role){
            $childRoles[] = array_merge($role->getChildRoles(), $role->getNestedRoles());
        }
        $roles = array_merge([], ...$childRoles);
        return array_key_exists($roleName, $roles);
    }

}
