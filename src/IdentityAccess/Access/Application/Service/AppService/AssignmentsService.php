<?php

declare(strict_types=1);

namespace App\IdentityAccess\Access\Application\Service\AppService;

use App\IdentityAccess\Access\Application\Service\AccessRightsServiceInterface;
use App\IdentityAccess\Access\Application\Service\AssignmentsServiceInterface;
use App\IdentityAccess\Access\Application\Service\PermissionDTO;
use App\IdentityAccess\Access\Application\Service\RoleDTO;
use App\IdentityAccess\Access\Application\Service\UserAssignmentsDTO;
use App\IdentityAccess\Access\Domain\Exception\NotExistItemException;
use App\IdentityAccess\User\Application\Service\UserQueryServiceInterface;
use App\IdentityAccess\User\Domain\User;
use Override;
use RuntimeException;
use Yiisoft\Rbac\AssignmentsStorageInterface;
use Yiisoft\Rbac\Manager;

final readonly class AssignmentsService implements AssignmentsServiceInterface
{
    public function __construct(
        private AssignmentsStorageInterface $assignmentsStorage,
        private AccessRightsServiceInterface $accessRightsService,
        private UserQueryServiceInterface $userQueryService,
        private Manager $manager,
    ) {
    }

    #[Override]
    public function getUserIdsByRole(RoleDTO $roleDTO): array
    {
        return $this->manager->getUserIdsByRoleName($roleDTO->getName());
    }

    /**
     * @return array<string, RoleDTO>
     */
    #[Override]
    public function getRolesByUser(string|int $userId): array
    {
        $roles = [];
        foreach ($this->manager->getRolesByUserId($userId) as $role) {
            $roleDTO = $this->accessRightsService->getRoleByName($role->getName());
            if ($roleDTO === null) {
                continue;
            }
            $roles[$role->getName()] = $roleDTO;
        }

        return $roles;
    }

    #[Override]
    public function getPermissionsByUser(string|int $userId): array
    {
        $permissions = [];
        foreach ($this->manager->getPermissionsByUserId($userId) as $permission) {
            $permissionName = $permission->getName();
            $permissions[$permissionName] = new PermissionDTO($permissionName);
        }
        return $permissions;
    }

    #[Override]
    public function userHasPermission(string|int $userId, string $permissionName): bool
    {
        return $this->manager->userHasPermission($userId, $permissionName);
    }

    #[Override]
    public function userHasRole(string|int $userId, string $roleName): bool
    {
        return $this->assignmentsStorage->get($roleName, (string)$userId) !== null;
    }

    #[Override]
    public function isAssignedRoleToUsers(RoleDTO $roleDTO): bool
    {
        return $this->assignmentsStorage->hasItem($roleDTO->getName());
    }

    /**
     * @throws NotExistItemException
     */
    #[Override]
    public function isAssignedPermissionToUsers(PermissionDTO $permissionDTO): bool
    {
        return $this->assignmentsStorage->hasItem($permissionDTO->getName());
    }

    #[Override]
    public function getUserAssignments(User $user): UserAssignmentsDTO
    {
        if (($userId = $user->getId()) === null) {
            throw new RuntimeException('User id is null');
        }

        $rolesDTO = $this->getRolesByUser($userId);
        //getByUserId method is used instead of getPermissionsByUser, so as not to load inherited permissions
        $userAssignments = $this->assignmentsStorage->getByUserId((string)$userId);
        if (empty($rolesDTO) && empty($userAssignments)) {
            return new UserAssignmentsDTO($user);
        }

        $permissionsDTO = [];
        foreach ($userAssignments as $assignment) {
            $permission = $this->accessRightsService->getPermissionByName($assignment->getItemName());
            $permission === null ?: $permissionsDTO[] = $permission;
        }

        return new UserAssignmentsDTO($user, $rolesDTO, $permissionsDTO);
    }

    #[Override]
    public function getAssignments(): array
    {
        /** @var array<string, mixed> $assignments */
        $assignments = $this->assignmentsStorage->getAll();

        // Приводим ID пользователей к целым числам
        $userIds = array_map('intval', array_filter(array_keys($assignments), 'is_numeric'));

        /** @var array<int, User> $users */
        $users = [];
        if ($userIds !== []) {
            $users = $this->userQueryService->getUsers($userIds);
        }

        $usersDTO = [];
        foreach ($users as $user) {
            $userId = $user->getId();
            if ($userId === null) {
                continue;
            }

            if (isset($assignments[(string)$userId])) {
                /** @var array<string, mixed> $userAssignments */
                $userAssignments = $assignments[(string)$userId];
                $roles = [];
                $permissions = [];

                foreach (array_keys($userAssignments) as $name) {
                    $role = $this->accessRightsService->getRoleByName($name);
                    if ($role !== null) {
                        $roles[] = $role;
                    }

                    $permission = $this->accessRightsService->getPermissionByName($name);
                    if ($permission !== null) {
                        $permissions[] = $permission;
                    }
                }

                $usersDTO[$userId] = new UserAssignmentsDTO($user, $roles, $permissions);
            }
        }

        return $usersDTO;
    }
}
