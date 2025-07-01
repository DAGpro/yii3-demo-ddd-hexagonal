<?php

declare(strict_types=1);

namespace App\IdentityAccess\Access\Application\Service;

use App\IdentityAccess\User\Domain\User;

final readonly class UserAssignmentsDTO
{
    public function __construct(private User $user, private array $roles = [], private array $permissions = [])
    {
    }

    public function getId(): string
    {
        return (string)$this->user->getId();
    }

    public function getLogin(): string
    {
        return $this->user->getLogin();
    }

    public function existRoles(): bool
    {
        return !empty($this->roles);
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getRolesName(): string
    {
        $rolesName = '';
        foreach ($this->roles as $role) {
            $rolesName .= $role->getName() . ', ';
        }

        return substr_replace($rolesName, '', strlen($rolesName) - 2, 2);
    }

    public function getChildRolesName(): string
    {
        $rolesName = '';
        foreach ($this->roles as $role) {
            if ($role->getChildRolesName()){
                $rolesName .= $role->getChildRolesName() . ', ';
            }
        }

        return substr_replace($rolesName, '', strlen($rolesName) - 2, 2);
    }

    public function getNestedRolesName(): string
    {
        $rolesName = '';
        foreach ($this->roles as $role) {
            if ($role->getNestedRolesName()){
                $rolesName .= $role->getNestedRolesName() . ', ';
            }
        }

        return substr_replace($rolesName, '', strlen($rolesName) - 2, 2);
    }

    public function existPermissions(): bool
    {
        return !empty($this->permissions);
    }

    public function getPermissions(): array
    {
        return $this->permissions;
    }

    public function getPermissionsName(): string
    {
        $permissionsName = '';
        foreach ($this->permissions as $permission) {
            $permissionsName .= $permission->getName() . ', ';
        }

        return substr_replace($permissionsName, '', strlen($permissionsName) - 2, 2);
    }

    public function getChildPermissionsName(): string
    {
        $permissionsName = '';
        foreach ($this->roles as $role) {
            if ($role->getChildPermissionsName()) {
                $permissionsName .= $role->getChildPermissionsName() . ', ';
            }
        }

        return substr_replace($permissionsName, '', strlen($permissionsName) - 2, 2);
    }

    public function getNestedPermissionsName(): string
    {
        $permissionsName = '';
        foreach ($this->roles as $role) {
            if ($role->getNestedPermissonsName()) {
                $permissionsName .= $role->getNestedPermissionsName() . ', ';
            }
        }

        return substr_replace($permissionsName, '', strlen($permissionsName) - 2, 2);
    }
}
