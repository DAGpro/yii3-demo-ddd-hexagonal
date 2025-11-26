<?php

declare(strict_types=1);

namespace App\IdentityAccess\Access\Slice\Service\AppService;

use App\IdentityAccess\Access\Slice\Service\AccessRightsServiceInterface;
use App\IdentityAccess\Access\Slice\Service\PermissionDTO;
use App\IdentityAccess\Access\Slice\Service\RoleDTO;
use Override;
use Yiisoft\Rbac\ItemsStorageInterface;
use Yiisoft\Rbac\Manager;
use Yiisoft\Rbac\Role;

final readonly class AccessRightsService implements AccessRightsServiceInterface
{
    public function __construct(
        private Manager $manager,
        private ItemsStorageInterface $storage,
    ) {}

    #[Override]
    public function existRole(string $roleName): bool
    {
        return $this->storage->getRole($roleName) !== null;
    }

    #[Override]
    public function getRoleByName(string $roleName): ?RoleDTO
    {
        $role = $this->storage->getRole($roleName);
        if ($role !== null) {
            $roleDTO = new RoleDTO(
                $role->getName(),
                $role->getDescription(),
            );
            $roleDTO->withChildRoles($this->getChildRoles($roleDTO));
            $roleDTO->withNestedRoles($this->getNestedRoles($roleDTO));
            $roleDTO->withChildPermissions($this->getPermissionsByRole($roleDTO));
            $roleDTO->withNestedPermissions($this->getNestedPermissionsByRole($roleDTO));
            return $roleDTO;
        }

        return null;
    }

    #[Override]
    public function getRoles(): array
    {
        $roles = [];
        foreach ($this->storage->getRoles() as $role) {
            $roleDTO = new RoleDTO($role->getName());
            $roleDTO->withChildRoles($this->getChildRoles($roleDTO));
            $roleDTO->withNestedRoles($this->getNestedRoles($roleDTO));
            $roleDTO->withChildPermissions($this->getPermissionsByRole($roleDTO));
            $roleDTO->withNestedPermissions($this->getNestedPermissionsByRole($roleDTO));

            $roles[$role->getName()] = $roleDTO;
        }
        return $roles;
    }

    #[Override]
    public function existPermission(string $permissionName): bool
    {
        return $this->getPermissionByName($permissionName) !== null;
    }

    #[Override]
    public function getPermissionByName(string $permissionName): ?PermissionDTO
    {
        $permission = $this->storage->getPermission($permissionName);
        return $permission === null ? null : new PermissionDTO($permission->getName(), $permission->getDescription());
    }

    #[Override]
    public function getPermissions(): array
    {
        $permissionDTO = [];
        foreach ($this->storage->getPermissions() as $permission) {
            $permissionName = $permission->getName();
            $permissionDTO[$permissionName] = new PermissionDTO($permissionName);
        }
        return $permissionDTO;
    }

    /**
     * @return array<string, RoleDTO>
     */
    #[Override]
    public function getChildRoles(RoleDTO $roleDTO): array
    {
        $roles = [];
        foreach ($this->storage->getDirectChildren($roleDTO->getName()) as $item) {
            if ($item instanceof Role) {
                $roleName = $item->getName();
                $roles[$roleName] = new RoleDTO($roleName);
            }
        }
        return $roles;
    }

    #[Override]
    public function getNestedRoles(RoleDTO $roleDTO): array
    {
        $roles = [];
        $childRoles = $this->storage->getDirectChildren($roleDTO->getName());
        foreach ($this->manager->getChildRoles($roleDTO->getName()) as $role) {
            if (array_key_exists($role->getName(), $childRoles)) {
                continue;
            }
            $roleName = $role->getName();
            $roles[$roleName] = new RoleDTO($roleName);
        }
        return $roles;
    }

    #[Override]
    public function getPermissionsByRole(RoleDTO $roleDTO): array
    {
        $permissions = [];
        foreach ($this->storage->getDirectChildren($roleDTO->getName()) as $item) {
            if ($item instanceof Role) {
                continue;
            }
            $permissionName = $item->getName();
            $permissions[$permissionName] = new PermissionDTO($permissionName);
        }
        return $permissions;
    }

    /**
     * @return array<string, PermissionDTO>
     */
    #[Override]
    public function getNestedPermissionsByRole(RoleDTO $roleDTO): array
    {
        $permissions = [];
        $childPermissions = $this->storage->getDirectChildren($roleDTO->getName());
        foreach ($this->manager->getPermissionsByRoleName($roleDTO->getName()) as $permission) {
            if ($permission instanceof Role || array_key_exists($permission->getName(), $childPermissions)) {
                continue;
            }
            $permissionName = $permission->getName();
            $permissions[$permissionName] = new PermissionDTO($permissionName);
        }
        return $permissions;
    }

    #[Override]
    public function hasChildren(RoleDTO $parentDTO): bool
    {
        return $this->storage->hasChildren($parentDTO->getName());
    }

    #[Override]
    public function setDefaultRoles(array $roles): self
    {
        $this->manager->setDefaultRoleNames($roles);
        return $this;
    }

    #[Override]
    public function getDefaultRoleNames(): array
    {
        return $this->manager->getDefaultRoleNames();
    }

    #[Override]
    public function getDefaultRoles(): array
    {
        $result = [];
        foreach ($this->manager->getDefaultRoles() as $role) {
            $roleDTO = new RoleDTO($role->getName());
            $roleDTO->withChildRoles($this->getChildRoles($roleDTO));
            $roleDTO->withNestedRoles($this->getNestedRoles($roleDTO));
            $roleDTO->withChildPermissions($this->getPermissionsByRole($roleDTO));
            $roleDTO->withNestedPermissions($this->getNestedPermissionsByRole($roleDTO));
            $result[$role->getName()] = $roleDTO;
        }
        return $result;
    }

}
