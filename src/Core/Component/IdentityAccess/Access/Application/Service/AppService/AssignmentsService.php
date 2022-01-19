<?php

namespace App\Core\Component\IdentityAccess\Access\Application\Service\AppService;

use App\Core\Component\IdentityAccess\Access\Application\Service\AccessRightsServiceInterface;
use App\Core\Component\IdentityAccess\Access\Application\Service\AssignmentsServiceInterface;
use App\Core\Component\IdentityAccess\Access\Application\Service\PermissionDTO;
use App\Core\Component\IdentityAccess\Access\Application\Service\RoleDTO;
use App\Core\Component\IdentityAccess\Access\Application\Service\UserAssignmentsDTO;
use App\Core\Component\IdentityAccess\Access\Domain\Exception\NotExistItemException;
use App\Core\Component\IdentityAccess\User\Application\Service\UserQueryServiceInterface;
use App\Core\Component\IdentityAccess\User\Domain\User;
use Yiisoft\Rbac\AssignmentsStorageInterface;
use Yiisoft\Rbac\Manager;

class AssignmentsService implements AssignmentsServiceInterface
{

    private AssignmentsStorageInterface $assignmentsStorage;
    private AccessRightsServiceInterface $accessRightsService;
    private Manager $manager;
    private UserQueryServiceInterface $userQueryService;

    public function __construct(
        AssignmentsStorageInterface $assignmentsStorage,
        AccessRightsServiceInterface $accessRightsService,
        UserQueryServiceInterface $userQueryService,
        Manager $manager
    ) {
        $this->assignmentsStorage = $assignmentsStorage;
        $this->accessRightsService = $accessRightsService;
        $this->userQueryService = $userQueryService;
        $this->manager = $manager;
    }


    public function getUserIdsByRole(RoleDTO $roleDTO): array
    {
        return $this->manager->getUserIdsByRole($roleDTO->getName());
    }

    public function getRolesByUser(string $userId): array
    {
        $roles = [];
        foreach ($this->manager->getRolesByUser($userId) as $role) {
            $roleDTO = $this->accessRightsService->getRoleByName($role->getName());
            $roles[$role->getName()] = $roleDTO;
        }
        return $roles;
    }

    public function getPermissionsByUser(string $userId): array
    {
        $permissions = [];
        foreach ($this->manager->getPermissionsByUser($userId) as $permission) {
            $permissionName = $permission->getName();
            $permissions[$permissionName] = new PermissionDTO($permissionName);
        }
        return $permissions;
    }

    public function userHasPermission(string $userId, string $permissionName): bool
    {
        return $this->manager->userHasPermission($userId, $permissionName);
    }

    public function userHasRole(string $userId, string $roleName): bool
    {
        return $this->assignmentsStorage->getUserAssignmentByName($userId, $roleName) !== null;
    }

    public function isAssignedRoleToUsers(RoleDTO $roleDTO): bool
    {
        return $this->assignmentExist($roleDTO->getName());
    }

    /**
     * @throws NotExistItemException
     */
    public function isAssignedPermissionToUsers(PermissionDTO $permissionDTO): bool
    {
        return $this->assignmentsStorage->assignmentExist($permissionDTO->getName());
    }

    public function userAssignmentExist(string $userId, string $roleName): bool
    {
        return $this->assignmentsStorage->getUserAssignmentByName($userId, $roleName) !== null;
    }

    public function assignmentExist(string $itemName): bool
    {
        return $this->assignmentsStorage->assignmentExist($itemName);
    }

    public function getUserAssignments(User $user): UserAssignmentsDTO
    {
        $rolesDTO = $this->getRolesByUser($user->getId());
        //getUserAssignments method is used instead of getPermissionsByUser, so as not to load inherited permissions
        $userAssignments = $this->assignmentsStorage->getUserAssignments($user->getId());
        if(empty($rolesDTO) && empty($userAssignments)) {
            return new UserAssignmentsDTO($user);
        }

        $permissionsDTO = [];
        foreach ($userAssignments as $assignment) {
            $permission = $this->accessRightsService->getPermissionByName($assignment->getItemName());
            $permission === null  ?: $permissionsDTO[] = $permission;
        }

        return new UserAssignmentsDTO($user, $rolesDTO, $permissionsDTO);
    }

    public function getAssignments(): array
    {
        $assignments = $this->assignmentsStorage->getAssignments();

        $userIds = array_keys($assignments);
        $users = $this->userQueryService->getUsers($userIds);

        $usersDTO = [];
        foreach ($users as $user) {
            if (array_key_exists($user->getId(), $assignments)) {
                $userAssignments = $assignments[$user->getId()];
                $roles = [];
                $permissions = [];
                foreach ($userAssignments as $name => $assignment){
                    $role = $this->accessRightsService->getRoleByName($name);
                    $role === null ?: $roles[] = $role;

                    $permission = $this->accessRightsService->getPermissionByName($name);
                    $permission === null  ?: $permissions[] = $permission;
                }
                $usersDTO[] = new UserAssignmentsDTO($user, $roles, $permissions);
            }
        }

        return $usersDTO;
    }
}
