<?php

declare(strict_types=1);

namespace App\Tests\Unit\IdentityAccess\Access\Application\Service;

use App\IdentityAccess\Access\Application\Service\PermissionDTO;
use App\IdentityAccess\Access\Application\Service\RoleDTO;
use App\Tests\UnitTester;
use Codeception\Test\Unit;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionClass;

#[CoversClass(RoleDTO::class)]
final class RoleDTOTest extends Unit
{
    protected UnitTester $tester;

    public function testCreateRoleDTOWithAllFields(): void
    {
        $name = 'admin';
        $description = 'Administrator role';
        $createdAt = 1620000000;
        $updatedAt = 1620003600;

        $dto = new RoleDTO($name, $description, $createdAt, $updatedAt);

        $this->assertSame($name, $dto->getName());
        $this->assertSame($description, $dto->getDescription());
        $this->assertSame($createdAt, $dto->getCreatedAt());
        $this->assertSame($updatedAt, $dto->getUpdatedAt());
    }

    public function testCreateRoleDTOWithRequiredFieldsOnly(): void
    {
        $name = 'user';
        $dto = new RoleDTO($name);

        $this->assertSame($name, $dto->getName());
        $this->assertSame('', $dto->getDescription());
        $this->assertNull($dto->getCreatedAt());
        $this->assertNull($dto->getUpdatedAt());
    }

    public function testWithChildRoles(): void
    {
        $role1 = new RoleDTO('editor');
        $role2 = new RoleDTO('moderator');

        $dto = new RoleDTO('admin');
        $dto->withChildRoles([$role1, $role2]);

        $this->assertCount(2, $dto->getChildRoles());
        $this->assertSame($role1, $dto->getChildRoles()[0]);
        $this->assertSame($role2, $dto->getChildRoles()[1]);
    }

    public function testWithNestedRoles(): void
    {
        $role1 = new RoleDTO('editor');
        $role2 = new RoleDTO('moderator');

        $dto = new RoleDTO('admin');
        $dto->withNestedRoles([$role1, $role2]);

        $this->assertCount(2, $dto->getNestedRoles());
        $this->assertSame($role1, $dto->getNestedRoles()[0]);
        $this->assertSame($role2, $dto->getNestedRoles()[1]);
    }

    public function testWithChildPermissions(): void
    {
        $permission1 = new PermissionDTO('create_post');
        $permission2 = new PermissionDTO('edit_post');

        $dto = new RoleDTO('admin');
        $dto->withChildPermissions([$permission1, $permission2]);

        $this->assertCount(2, $dto->getChildPermissions());
        $this->assertSame($permission1, $dto->getChildPermissions()[0]);
        $this->assertSame($permission2, $dto->getChildPermissions()[1]);
    }

    public function testWithNestedPermissions(): void
    {
        $permission1 = new PermissionDTO('create_post');
        $permission2 = new PermissionDTO('edit_post');

        $dto = new RoleDTO('admin');
        $dto->withNestedPermissions(['create' => $permission1, 'edit' => $permission2]);

        $this->assertCount(2, $dto->getNestedPermissions());
        $this->assertSame($permission1, $dto->getNestedPermissions()['create']);
        $this->assertSame($permission2, $dto->getNestedPermissions()['edit']);
    }

    public function testGetChildRolesName(): void
    {
        $role1 = new RoleDTO('editor');
        $role2 = new RoleDTO('moderator');

        $dto = new RoleDTO('admin');
        $dto->withChildRoles([$role1, $role2]);

        $this->assertSame('editor, moderator', $dto->getChildRolesName());
    }

    public function testGetChildRolesNameEmpty(): void
    {
        $dto = new RoleDTO('admin');
        $dto->withChildRoles([]);

        $this->assertSame('', $dto->getChildRolesName());
    }

    public function testGetNestedRolesNameEmpty(): void
    {
        $dto = new RoleDTO('admin');
        $reflection = new ReflectionClass($dto);
        $property = $reflection->getProperty('nestedRoles');
        $property->setValue($dto, []);

        $this->assertSame('', $dto->getNestedRolesName());
    }

    public function testGetNestedRolesName(): void
    {
        $role1 = new RoleDTO('moderator');
        $role2 = new RoleDTO('editor');

        $dto = new RoleDTO('admin');
        $dto->withNestedRoles([$role1, $role2]);

        $this->assertSame('moderator, editor', $dto->getNestedRolesName());
    }

    public function testGetChildPermissionsName(): void
    {
        $permission1 = new PermissionDTO('create_post');
        $permission2 = new PermissionDTO('edit_post');

        $dto = new RoleDTO('admin');
        $dto->withChildPermissions([$permission1, $permission2]);

        $this->assertSame('create_post, edit_post', $dto->getChildPermissionsName());
    }

    public function testGetChildPermissionsNameEmpty(): void
    {
        $dto = new RoleDTO('admin');
        $dto->withChildPermissions([]);

        $this->assertSame('', $dto->getChildPermissionsName());
    }

    public function testGetNestedPermissionsName(): void
    {
        $permission1 = new PermissionDTO('create_post');
        $permission2 = new PermissionDTO('edit_post');

        $dto = new RoleDTO('admin');
        $dto->withNestedPermissions(['create' => $permission1, 'edit' => $permission2]);

        $this->assertSame('create_post, edit_post', $dto->getNestedPermissionsName());
    }

    public function testGetNestedPermissionsNameEmpty(): void
    {
        $dto = new RoleDTO('admin');
        $reflection = new ReflectionClass($dto);
        $property = $reflection->getProperty('nestedPermissions');
        $property->setValue($dto, []);

        $this->assertSame('', $dto->getNestedPermissionsName());
    }

    public function testGetChildRolesNameWithPermissionName(): void
    {
        $role1 = new RoleDTO('moderator');
        $role2 = new RoleDTO('editor');
        $permission1 = new PermissionDTO('create_post');
        $permission2 = new PermissionDTO('edit_post');

        $dto = new RoleDTO('admin');
        $dto->withChildRoles([$role1, $role2]);
        $dto->withChildPermissions([$permission1, $permission2]);

        $expected = [
            'moderator[ permissions: create_post, edit_post]',
            'editor[ permissions: create_post, edit_post]',
        ];

        $this->assertSame($expected, $dto->getChildRolesNameWithPermissionName());
    }

    public function testGetChildRolesNameWithPermissionNameEmpty(): void
    {
        $dto = new RoleDTO('admin');
        $dto->withChildRoles([]);

        $this->assertSame([], $dto->getChildRolesNameWithPermissionName());
    }
}
