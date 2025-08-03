<?php

declare(strict_types=1);

namespace App\IdentityAccess\Access\Application\Service;

final class RoleDTO
{
    /**
     * @var array<RoleDTO>
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private array $childRoles = [];
    /**
     * @var array<PermissionDTO>
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private array $childPermissions = [];

    /**
     * @var array<PermissionDTO>
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private array $nestedPermissions = [];

    /**
     * @var array<RoleDTO>
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private array $nestedRoles = [];

    public function __construct(
        private readonly string $name,
        private readonly ?string $description = null,
        private readonly ?int $created_at = null,
        private readonly ?int $updated_at = null,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param array<RoleDTO> $roles
     */
    public function withChildRoles(array $roles): void
    {
        $this->childRoles = $roles;
    }

    /**
     * @param array<RoleDTO> $roles
     */
    public function withNestedRoles(array $roles): void
    {
        $this->nestedRoles = $roles;
    }

    /**
     * @param array<PermissionDTO> $permissions
     */
    public function withChildPermissions(array $permissions): void
    {
        $this->childPermissions = $permissions;
    }

    /**
     * @param array<string,PermissionDTO> $nestedPermissions
     */
    public function withNestedPermissions(array $nestedPermissions): void
    {
        $this->nestedPermissions = $nestedPermissions;
    }

    public function getChildRoles(): array
    {
        return $this->childRoles;
    }

    public function getNestedRoles(): array
    {
        return $this->nestedRoles;
    }

    public function getChildRolesName(): string
    {
        $rolesName = '';
        foreach ($this->childRoles as $role) {
            $rolesName .= $role->getName() . ', ';
        }

        return substr_replace($rolesName, '', strlen($rolesName) - 2, 2);
    }

    public function getNestedRolesName(): string
    {
        $rolesName = '';
        foreach ($this->nestedRoles as $role) {
            $rolesName .= $role->getName() . ', ';
        }

        return substr_replace($rolesName, '', strlen($rolesName) - 2, 2);
    }

    /**
     * @return PermissionDTO[]
     */
    public function getChildPermissions(): array
    {
        return $this->childPermissions;
    }

    /**
     * @return PermissionDTO[]
     */
    public function getNestedPermissions(): array
    {
        return $this->nestedPermissions;
    }

    public function getChildPermissionsName(): string
    {
        $permissionsName = '';
        foreach ($this->getChildPermissions() as $permission) {
            $permissionsName .= $permission->getName() . ', ';
        }

        return substr_replace($permissionsName, '', strlen($permissionsName) - 2, 2);
    }

    public function getNestedPermissionsName(): string
    {
        $permissionsName = '';
        foreach ($this->getNestedPermissions() as $permission) {
            $permissionsName .= $permission->getName() . ', ';
        }

        return substr_replace($permissionsName, '', strlen($permissionsName) - 2, 2);
    }

    public function getChildRolesNameWithPermissionName(): array
    {
        $rolesName = [];
        foreach ($this->childRoles as $role) {
            $rolesName[] = $role->getName()
                . '[ permissions: '
                . $this->getChildPermissionsName()
                . ']';
        }

        return $rolesName;
    }

    public function getDescription(): string
    {
        return $this->description ?: '';
    }

    public function getCreatedAt(): ?int
    {
        return $this->created_at;
    }

    public function getUpdatedAt(): ?int
    {
        return $this->updated_at;
    }


}
