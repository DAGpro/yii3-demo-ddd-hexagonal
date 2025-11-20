<?php

declare(strict_types=1);

namespace App\Tests\Unit\IdentityAccess\Access\Application\Service;

use App\IdentityAccess\Access\Application\Service\PermissionDTO;
use App\IdentityAccess\Access\Application\Service\RoleDTO;
use App\IdentityAccess\Access\Application\Service\UserAssignmentsDTO;
use App\IdentityAccess\User\Domain\User;
use App\Tests\UnitTester;
use Codeception\Test\Unit;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionClass;

#[CoversClass(UserAssignmentsDTO::class)]
final class UserAssignmentsDTOTest extends Unit
{
    private static ?User $user = null;

    protected UnitTester $tester;
    private array $roles;

    private array $permissions;

    public function testCreateUserAssignmentsDTO(): void
    {
        $dto = new UserAssignmentsDTO(self::$user, $this->roles, $this->permissions);

        $this->assertSame('1', $dto->getId());

        $this->assertSame('testuser', $dto->getLogin());

        $this->assertSame($this->roles, $dto->getRoles());
        $this->assertSame($this->permissions, $dto->getPermissions());
    }

    public function testExistRolesWithRoles(): void
    {
        $dto = new UserAssignmentsDTO(self::$user, $this->roles);
        $this->assertTrue($dto->existRoles());
    }

    public function testExistRolesWithoutRoles(): void
    {
        $dto = new UserAssignmentsDTO(self::$user, []);
        $this->assertFalse($dto->existRoles());
    }

    public function testGetRolesName(): void
    {
        $dto = new UserAssignmentsDTO(self::$user, $this->roles);
        $this->assertSame('admin, editor', $dto->getRolesName());
    }

    public function testGetRolesNameEmpty(): void
    {
        $dto = new UserAssignmentsDTO(self::$user, []);
        $this->assertSame('', $dto->getRolesName());
    }

    public function testGetChildRolesName(): void
    {
        $role1 = new RoleDTO('admin');
        $role1->withChildRoles([new RoleDTO('editor')]);

        $dto = new UserAssignmentsDTO(self::$user, [$role1]);
        $this->assertSame('editor', $dto->getChildRolesName());
    }

    public function testGetChildRolesNameEmpty(): void
    {
        $dto = new UserAssignmentsDTO(self::$user, $this->roles);
        $this->assertSame('', $dto->getChildRolesName());
    }

    public function testGetNestedRolesName(): void
    {
        $role1 = new RoleDTO('admin');
        $role1->withNestedRoles([new RoleDTO('editor')]);

        $dto = new UserAssignmentsDTO(self::$user, [$role1]);
        $this->assertSame('editor', $dto->getNestedRolesName());
    }

    public function testGetNestedRolesNameEmpty(): void
    {
        $dto = new UserAssignmentsDTO(self::$user, $this->roles);
        $this->assertSame('', $dto->getNestedRolesName());
    }

    public function testExistPermissionsWithPermissions(): void
    {
        $dto = new UserAssignmentsDTO(self::$user, [], $this->permissions);
        $this->assertTrue($dto->existPermissions());
    }

    public function testExistPermissionsWithoutPermissions(): void
    {
        $dto = new UserAssignmentsDTO(self::$user, [], []);
        $this->assertFalse($dto->existPermissions());
    }

    public function testGetPermissionsName(): void
    {
        $dto = new UserAssignmentsDTO(self::$user, [], $this->permissions);
        $this->assertSame('create_post, edit_post', $dto->getPermissionsName());
    }

    public function testGetPermissionsNameEmpty(): void
    {
        $dto = new UserAssignmentsDTO(self::$user, [], []);
        $this->assertSame('', $dto->getPermissionsName());
    }

    public function testGetChildPermissionsName(): void
    {
        $role1 = new RoleDTO('admin');
        $role1->withChildPermissions([
            new PermissionDTO('create_post', 'Create posts'),
            new PermissionDTO('edit_post', 'Edit posts'),
        ]);

        $role2 = new RoleDTO('editor');
        $role2->withChildPermissions([
            new PermissionDTO('view_post', 'View posts'),
        ]);

        $dto = new UserAssignmentsDTO(self::$user, [$role1, $role2]);
        $this->assertSame('create_post, edit_post, view_post', $dto->getChildPermissionsName());
    }

    public function testGetChildPermissionsNameWithEmptyRoles(): void
    {
        $dto = new UserAssignmentsDTO(self::$user, []);
        $this->assertSame('', $dto->getChildPermissionsName());
    }

    public function testGetChildPermissionsNameWithRolesWithoutChildPermissions(): void
    {
        $role1 = new RoleDTO('admin');
        $role2 = new RoleDTO('editor');
        $dto = new UserAssignmentsDTO(self::$user, [$role1, $role2]);
        $this->assertSame('', $dto->getChildPermissionsName());
    }

    public function testGetNestedPermissionsName(): void
    {
        $role1 = new RoleDTO('admin');
        $role1->withNestedPermissions([
            new PermissionDTO('create_user', 'Create users'),
            new PermissionDTO('edit_user', 'Edit users'),
        ]);

        $role2 = new RoleDTO('manager');
        $role2->withNestedPermissions([
            new PermissionDTO('view_reports', 'View reports'),
        ]);

        $dto = new UserAssignmentsDTO(self::$user, [$role1, $role2]);
        $this->assertSame('create_user, edit_user, view_reports', $dto->getNestedPermissionsName());
    }

    public function testGetNestedPermissionsNameWithEmptyRoles(): void
    {
        $dto = new UserAssignmentsDTO(self::$user, []);
        $this->assertSame('', $dto->getNestedPermissionsName());
    }

    public function testGetNestedPermissionsNameWithRolesWithoutNestedPermissions(): void
    {
        $role1 = new RoleDTO('admin');
        $role2 = new RoleDTO('editor');
        $dto = new UserAssignmentsDTO(self::$user, [$role1, $role2]);
        $this->assertSame('', $dto->getNestedPermissionsName());
    }

    protected function _before(): void
    {
        if (self::$user === null) {
            self::$user = $this->createUser('testuser');
        }

        $this->roles = [
            new RoleDTO('admin', 'Administrator'),
            new RoleDTO('editor', 'Content Editor'),
        ];

        $this->permissions = [
            new PermissionDTO('create_post', 'Create posts'),
            new PermissionDTO('edit_post', 'Edit posts'),
        ];
    }

    private function createUser(string $login): User
    {
        $user = new User($login, 'password123');

        //We use reflection to install secure properties
        $reflection = new ReflectionClass($user);

        // Install ID
        $idProperty = $reflection->getProperty('id');
        $idProperty->setValue($user, 1);

        return $user;
    }
}
