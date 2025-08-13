<?php

declare(strict_types=1);

namespace App\Tests\Unit\IdentityAccess\Access\Application\Service;

use App\IdentityAccess\Access\Application\Service\PermissionDTO;
use App\IdentityAccess\Access\Application\Service\RoleDTO;
use App\IdentityAccess\Access\Application\Service\UserAssignmentsDTO;
use App\IdentityAccess\User\Domain\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[CoversClass(UserAssignmentsDTO::class)]
final class UserAssignmentsDTOTest extends TestCase
{
    private User $user;
    private array $roles;
    private array $permissions;

    public function testCreateUserAssignmentsDTO(): void
    {
        $dto = new UserAssignmentsDTO($this->user, $this->roles, $this->permissions);

        $this->assertSame('1', $dto->getId());

        $this->assertSame('testuser', $dto->getLogin());

        $this->assertSame($this->roles, $dto->getRoles());
        $this->assertSame($this->permissions, $dto->getPermissions());
    }

    public function testExistRolesWithRoles(): void
    {
        $dto = new UserAssignmentsDTO($this->user, $this->roles);
        $this->assertTrue($dto->existRoles());
    }

    public function testExistRolesWithoutRoles(): void
    {
        $dto = new UserAssignmentsDTO($this->user, []);
        $this->assertFalse($dto->existRoles());
    }

    public function testGetRolesName(): void
    {
        $dto = new UserAssignmentsDTO($this->user, $this->roles);
        $this->assertSame('admin, editor', $dto->getRolesName());
    }

    public function testGetRolesNameEmpty(): void
    {
        $dto = new UserAssignmentsDTO($this->user, []);
        $this->assertSame('', $dto->getRolesName());
    }

    public function testGetChildRolesName(): void
    {
        $role1 = new RoleDTO('admin');
        $role1->withChildRoles([new RoleDTO('editor')]);

        $dto = new UserAssignmentsDTO($this->user, [$role1]);
        $this->assertSame('editor', $dto->getChildRolesName());
    }

    public function testGetChildRolesNameEmpty(): void
    {
        $dto = new UserAssignmentsDTO($this->user, $this->roles);
        $this->assertSame('', $dto->getChildRolesName());
    }

    public function testGetNestedRolesName(): void
    {
        $role1 = new RoleDTO('admin');
        $role1->withNestedRoles([new RoleDTO('editor')]);

        $dto = new UserAssignmentsDTO($this->user, [$role1]);
        $this->assertSame('editor', $dto->getNestedRolesName());
    }

    public function testGetNestedRolesNameEmpty(): void
    {
        $dto = new UserAssignmentsDTO($this->user, $this->roles);
        $this->assertSame('', $dto->getNestedRolesName());
    }

    public function testExistPermissionsWithPermissions(): void
    {
        $dto = new UserAssignmentsDTO($this->user, [], $this->permissions);
        $this->assertTrue($dto->existPermissions());
    }

    public function testExistPermissionsWithoutPermissions(): void
    {
        $dto = new UserAssignmentsDTO($this->user, [], []);
        $this->assertFalse($dto->existPermissions());
    }

    public function testGetPermissionsName(): void
    {
        $dto = new UserAssignmentsDTO($this->user, [], $this->permissions);
        $this->assertSame('create_post, edit_post', $dto->getPermissionsName());
    }

    public function testGetPermissionsNameEmpty(): void
    {
        $dto = new UserAssignmentsDTO($this->user, [], []);
        $this->assertSame('', $dto->getPermissionsName());
    }

    protected function setUp(): void
    {
        $this->user = $this->createUser('testuser', 'Test User');

        $this->roles = [
            new RoleDTO('admin', 'Administrator'),
            new RoleDTO('editor', 'Content Editor'),
        ];

        $this->permissions = [
            new PermissionDTO('create_post', 'Create posts'),
            new PermissionDTO('edit_post', 'Edit posts'),
        ];
    }

    private function createUser(string $login, string $name): User
    {
        $user = new User($login, 'password123');
        // Используем рефлексию для установки защищенных свойств
        $reflection = new ReflectionClass($user);

        // Устанавливаем id
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($user, 1);

        // Устанавливаем login
        $loginProperty = $reflection->getProperty('login');
        $loginProperty->setAccessible(true);
        $loginProperty->setValue($user, $login);

        return $user;
    }
}
