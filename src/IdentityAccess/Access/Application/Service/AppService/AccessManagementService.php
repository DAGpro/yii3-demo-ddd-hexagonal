<?php

declare(strict_types=1);

namespace App\IdentityAccess\Access\Application\Service\AppService;

use App\IdentityAccess\Access\Application\Service\AccessManagementServiceInterface;
use App\IdentityAccess\Access\Application\Service\AccessRightsServiceInterface;
use App\IdentityAccess\Access\Application\Service\AssignmentsServiceInterface;
use App\IdentityAccess\Access\Application\Service\PermissionDTO;
use App\IdentityAccess\Access\Application\Service\RoleDTO;
use App\IdentityAccess\Access\Domain\Exception\AssignedItemException;
use App\IdentityAccess\Access\Domain\Exception\ExistItemException;
use App\IdentityAccess\Access\Domain\Exception\NotExistItemException;
use Override;
use Yiisoft\Rbac\ItemsStorageInterface;
use Yiisoft\Rbac\Manager;
use Yiisoft\Rbac\Permission;
use Yiisoft\Rbac\Role;

final readonly class AccessManagementService implements AccessManagementServiceInterface
{
    public function __construct(
        private Manager $manager,
        private ItemsStorageInterface $storage,
        private AccessRightsServiceInterface $accessRightsService,
        private AssignmentsServiceInterface $assignmentsService,
    ) {
    }

    /**
     * @throws ExistItemException
     */
    #[Override]
    public function addRole(RoleDTO $roleDTO): void
    {
        $this->throwExceptionIfExistRole($roleDTO);

        $role = new Role($roleDTO->getName());
        $this->manager->addRole($role);
    }

    /**
     * @throws AssignedItemException
     * @throws NotExistItemException
     */
    #[Override]
    public function removeRole(RoleDTO $roleDTO): void
    {
        $this->throwExceptionIfNotExistRole($roleDTO);

        if ($this->assignmentsService->isAssignedRoleToUsers($roleDTO)) {
            throw new AssignedItemException('This role is assigned to users.
                Change assign the role to users before deleting the role!',
            );
        }

        $role = new Role($roleDTO->getName());
        $this->manager->removeRole($role->getName());
    }

    /**
     * @throws ExistItemException
     */
    #[Override]
    public function addPermission(PermissionDTO $permissionDTO): void
    {
        $this->throwExceptionIfExistPermission($permissionDTO);

        $permission = new Permission($permissionDTO->getName());
        $this->manager->addPermission($permission);
    }

    /**
     * @throws AssignedItemException
     * @throws NotExistItemException
     */
    #[Override]
    public function removePermission(PermissionDTO $permissionDTO): void
    {
        $this->throwExceptionIfNotExistPermission($permissionDTO);

        if ($this->assignmentsService->isAssignedPermissionToUsers($permissionDTO)) {
            throw new AssignedItemException('This permission is assigned to users.
                change assign the permission to users before deleting the permission!',
            );
        }

        $permission = new Permission($permissionDTO->getName());
        $this->manager->removePermission($permission->getName());
    }

    /**
     * @throws ExistItemException
     * @throws NotExistItemException
     */
    #[Override]
    public function addChildRole(RoleDTO $parentDTO, RoleDTO $childDTO): void
    {
        $this->throwExceptionIfNotExistRole($parentDTO);
        $this->throwExceptionIfNotExistRole($childDTO);

        if (!$this->canAddChildRole($parentDTO, $childDTO)) {
            throw new ExistItemException('Unable to add child role!');
        }

        $parent = new Role($parentDTO->getName());
        $child = new Role($childDTO->getName());
        $this->manager->addChild($parent->getName(), $child->getName());
    }

    /**
     * @throws ExistItemException
     * @throws NotExistItemException
     */
    #[Override]
    public function addChildPermission(RoleDTO $parentDTO, PermissionDTO $childDTO): void
    {
        $this->throwExceptionIfNotExistRole($parentDTO);
        $this->throwExceptionIfNotExistPermission($childDTO);

        if (!$this->canAddChildPermission($parentDTO, $childDTO)) {
            throw new ExistItemException('Unable to add child permission!');
        }

        $parent = new Role($parentDTO->getName());
        $child = new Permission($childDTO->getName());
        $this->manager->addChild($parent->getName(), $child->getName());
    }

    /**
     * @throws NotExistItemException
     */
    #[Override]
    public function removeChildRole(RoleDTO $parentDTO, RoleDTO $childDTO): void
    {
        $this->throwExceptionIfNotExistRole($parentDTO);
        $this->throwExceptionIfNotExistRole($childDTO);

        if (!$this->hasChildRole($parentDTO, $childDTO)) {
            throw new NotExistItemException('The parent role does not have this child role!');
        }

        $parent = new Role($parentDTO->getName());
        $child = new Role($childDTO->getName());
        $this->manager->removeChild($parent->getName(), $child->getName());
    }

    /**
     * @throws NotExistItemException
     */
    #[Override]
    public function removeChildPermission(RoleDTO $parentRoleDTO, PermissionDTO $childPermissionDTO): void
    {
        $this->throwExceptionIfNotExistRole($parentRoleDTO);
        $this->throwExceptionIfNotExistPermission($childPermissionDTO);

        if (!$this->hasChildPermission($parentRoleDTO, $childPermissionDTO)) {
            throw new NotExistItemException('The parent role does not have this child permission!');
        }

        $parent = new Role($parentRoleDTO->getName());
        $child = new Permission($childPermissionDTO->getName());
        $this->manager->removeChild($parent->getName(), $child->getName());
    }

    /**
     * @throws NotExistItemException
     */
    #[Override]
    public function removeChildren(RoleDTO $parentDTO): void
    {
        $this->throwExceptionIfNotExistRole($parentDTO);

        $parent = new Role($parentDTO->getName());
        $this->manager->removeChildren($parent->getName());
    }

    #[Override]
    public function hasChildRole(RoleDTO $parentRoleDTO, RoleDTO $childRoleDTO): bool
    {
        $parentRole = new Role($parentRoleDTO->getName());
        $childRole = new Role($childRoleDTO->getName());
        return $this->manager->hasChild($parentRole->getName(), $childRole->getName());
    }

    #[Override]
    public function hasChildPermission(RoleDTO $parentRoleDTO, PermissionDTO $childPermissionDTO): bool
    {
        $parentRole = new Role($parentRoleDTO->getName());
        $childPermission = new Permission($childPermissionDTO->getName());
        return $this->manager->hasChild($parentRole->getName(), $childPermission->getName());
    }

    #[Override]
    public function clearAccessRights(): void
    {
        $this->storage->clear();
    }

    private function canAddChildRole(RoleDTO $parentRoleDTO, RoleDTO $childRoleDTO): bool
    {
        $parent = new Role($parentRoleDTO->getName());
        $child = new Role($childRoleDTO->getName());
        return $this->manager->canAddChild($parent->getName(), $child->getName());
    }

    private function canAddChildPermission(RoleDTO $parentRoleDTO, PermissionDTO $childPermissionDTO): bool
    {
        $parent = new Role($parentRoleDTO->getName());
        $child = new Permission($childPermissionDTO->getName());
        return $this->manager->canAddChild($parent->getName(), $child->getName());
    }

    /**
     * @throws ExistItemException
     */
    private function throwExceptionIfExistRole(RoleDTO $roleDTO): void
    {
        if ($this->accessRightsService->existRole($roleDTO->getName())) {
            throw new ExistItemException("Role '{$roleDTO->getName()}' already exists!");
        }
    }

    /**
     * @throws NotExistItemException
     */
    private function throwExceptionIfNotExistRole(RoleDTO $roleDTO): void
    {
        if (!$this->accessRightsService->existRole($roleDTO->getName())) {
            throw new NotExistItemException("Role '{$roleDTO->getName()}' not exists!");
        }
    }

    /**
     * @throws ExistItemException
     */
    private function throwExceptionIfExistPermission(PermissionDTO $permissionDTO): void
    {
        if ($this->accessRightsService->existPermission($permissionDTO->getName())) {
            throw new ExistItemException("Permission '{$permissionDTO->getName()}' already exists!");
        }
    }

    /**
     * @throws NotExistItemException
     */
    private function throwExceptionIfNotExistPermission(PermissionDTO $permissionDTO): void
    {
        if (!$this->accessRightsService->existPermission($permissionDTO->getName())) {
            throw new NotExistItemException("Permission '{$permissionDTO->getName()}' not exists!");
        }
    }

}
