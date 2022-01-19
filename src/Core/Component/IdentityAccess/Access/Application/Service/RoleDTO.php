<?php

declare(strict_types=1);

namespace App\Core\Component\IdentityAccess\Access\Application\Service;

class RoleDTO
{
    private string $name;
    private ?string $description;
    private ?int $created_at;
    private ?int $updated_at;
    private array $childRoles = [];
    private array $childPermissions = [];
    private array $nestedPermissions;
    private array $nestedRoles;

    public function __construct(
        string $name,
        ?string $description = null,
        ?int $created_at = null,
        ?int $updated_at = null
    ) {
        $this->name = $name;
        $this->description = $description;
        $this->created_at = $created_at;
        $this->updated_at = $updated_at;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function withChildRoles(array $roles): void
    {
        $this->childRoles = $roles;
    }

    public function withNestedRoles(array $roles): void
    {
        $this->nestedRoles = $roles;
    }

    public function withChildPermissions(array $permissions): void
    {
        $this->childPermissions = $permissions;
    }

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
        foreach ($this->childRoles as $role){
            $rolesName .= $role->getName() . ', ';
        }

        return substr_replace($rolesName, '', strlen($rolesName) - 2, 2);
    }

    public function getNestedRolesName(): string
    {
        $rolesName = '';
        foreach ($this->nestedRoles as $role){
            $rolesName .= $role->getName() . ', ';
        }

        return substr_replace($rolesName, '', strlen($rolesName) - 2, 2);
    }

    public function getChildPermissions(): array
    {
        return $this->childPermissions;
    }

    public function getNestedPermissions(): array
    {
        return $this->nestedPermissions;
    }

    public function getChildPermissionsName(): string
    {
        $permissionsName = '';
        foreach ($this->getChildPermissions() as $permission){
            $permissionsName .= $permission->getName() . ', ';
        }

        return substr_replace($permissionsName, '', strlen($permissionsName) - 2, 2);
    }

    public function getNestedPermissionsName(): string
    {
        $permissionsName = '';
        foreach ($this->getNestedPermissions() as $permission){
            $permissionsName .= $permission->getName() . ', ';
        }

        return substr_replace($permissionsName, '', strlen($permissionsName) - 2, 2);
    }

    public function getChildRolesNameWithPermissionName(): array
    {
        $rolesName = [];
        foreach ($this->childRoles as $role){
            $rolesName[] = $role->getName()
                . '[ permissions: '
                . $this->getChildPermissionsName()
                . ']';
        }

        return $rolesName;
    }

    public function getDescription(): string
    {
        return $this->description;
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
