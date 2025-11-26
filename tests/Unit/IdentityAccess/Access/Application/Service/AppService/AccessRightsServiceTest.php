<?php

declare(strict_types=1);

namespace App\Tests\Unit\IdentityAccess\Access\Application\Service\AppService;

use App\IdentityAccess\Access\Slice\Service\AppService\AccessRightsService;
use App\IdentityAccess\Access\Slice\Service\PermissionDTO;
use App\IdentityAccess\Access\Slice\Service\RoleDTO;
use App\Tests\UnitTester;
use Codeception\Test\Unit;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use Yiisoft\Rbac\AssignmentsStorageInterface;
use Yiisoft\Rbac\Exception\DefaultRolesNotFoundException;
use Yiisoft\Rbac\ItemsStorageInterface;
use Yiisoft\Rbac\Manager;
use Yiisoft\Rbac\Permission;
use Yiisoft\Rbac\Php\AssignmentsStorage;
use Yiisoft\Rbac\Php\ItemsStorage;
use Yiisoft\Rbac\Role;
use Yiisoft\Rbac\RuleFactoryInterface;

#[CoversClass(AccessRightsService::class)]
final class AccessRightsServiceTest extends Unit
{
    private static ?ItemsStorageInterface $storage = null;

    private static ?AssignmentsStorageInterface $assignmentsStorage = null;

    private static ?string $tempDir = null;

    protected UnitTester $tester;

    private AccessRightsService $service;

    private Manager $manager;

    public function testExistRoleWhenRoleExists(): void
    {
        $roleName = 'admin';
        $role = new Role($roleName);

        $this->manager->addRole($role);

        $this->assertTrue($this->service->existRole($roleName));
    }

    public function testExistRoleWhenRoleNotExists(): void
    {
        $roleName = 'nonexistent';

        $this->assertFalse($this->service->existRole($roleName));
    }

    public function testGetRoleByNameWhenRoleExists(): void
    {
        $roleName = 'admin';
        $role = new Role($roleName);

        $this->manager->addRole($role);
        $result = $this->service->getRoleByName($roleName);

        $this->assertInstanceOf(RoleDTO::class, $result);
        $this->assertSame($roleName, $result->getName());
    }

    public function testGetRoleByNameWhenRoleNotExists(): void
    {
        $roleName = 'nonexistent';

        $result = $this->service->getRoleByName($roleName);

        $this->assertNull($result);
    }

    public function testGetRoles(): void
    {
        $this->manager->addRole(new Role('admin'));
        $this->manager->addRole(new Role('editor'));

        $result = $this->service->getRoles();

        $this->assertCount(2, $result);
        $this->assertArrayHasKey('admin', $result);
        $this->assertArrayHasKey('editor', $result);
        $this->assertInstanceOf(RoleDTO::class, $result['admin']);
        $this->assertInstanceOf(RoleDTO::class, $result['editor']);
    }

    public function testExistPermissionWhenPermissionExists(): void
    {
        $permissionName = 'create_post';
        $permission = new Permission($permissionName);

        $this->manager->addPermission($permission);

        $this->assertTrue($this->service->existPermission($permissionName));
    }

    public function testExistPermissionWhenPermissionNotExists(): void
    {
        $permissionName = 'create_post';

        $this->assertFalse($this->service->existPermission($permissionName));
    }

    public function testGetPermissionByNameWhenPermissionExists(): void
    {
        $permissionName = 'create_post';
        $permission = new Permission($permissionName);

        $this->manager->addPermission($permission);

        $result = $this->service->getPermissionByName($permissionName);

        $this->assertInstanceOf(PermissionDTO::class, $result);
        $this->assertSame($permissionName, $result->getName());
    }

    public function testGetPermissionByNameWhenPermissionNotExists(): void
    {
        $permissionName = 'create_post';

        $result = $this->service->getPermissionByName($permissionName);
        $this->assertNull($result);
    }

    public function testSetDefaultRoles(): void
    {
        $roles = ['admin', 'user'];

        $result = $this->service->setDefaultRoles($roles);

        $this->assertSame($this->service, $result);
        $this->assertSame($roles, $this->service->getDefaultRoleNames());
    }

    public function testGetDefaultRoles(): void
    {
        $roles = ['admin', 'user'];
        $this->service->setDefaultRoles($roles);
        $this->manager->addRole(new Role('admin'));
        $this->manager->addRole(new Role('user'));

        $result = $this->service->getDefaultRoles();

        $this->assertCount(2, $result);
        $this->assertArrayHasKey('admin', $result);
        $this->assertArrayHasKey('user', $result);
        $this->assertInstanceOf(RoleDTO::class, $result['admin']);
        $this->assertInstanceOf(RoleDTO::class, $result['user']);
    }

    public function testGetDefaultRolesIfNotRolesInStorage(): void
    {
        $this->expectException(DefaultRolesNotFoundException::class);

        $roles = ['admin', 'user'];
        $this->service->setDefaultRoles($roles);

        $this->service->getDefaultRoles();
    }

    public function testGetPermissions(): void
    {
        $permission1 = new Permission('create_post');
        $permission2 = new Permission('edit_post');
        $this->manager->addPermission($permission1);
        $this->manager->addPermission($permission2);

        $result = $this->service->getPermissions();

        $this->assertCount(2, $result);
        $this->assertArrayHasKey('create_post', $result);
        $this->assertArrayHasKey('edit_post', $result);
        $this->assertInstanceOf(PermissionDTO::class, $result['create_post']);
        $this->assertInstanceOf(PermissionDTO::class, $result['edit_post']);
    }

    public function testGetChildRoles(): void
    {
        $parentRole = new Role('admin');
        $childRole = new Role('editor');
        $this->manager->addRole($parentRole);
        $this->manager->addRole($childRole);
        $this->manager->addChild($parentRole->getName(), $childRole->getName());

        $result = $this->service->getChildRoles(new RoleDTO('admin'));

        $this->assertArrayHasKey('editor', $result);
        $this->assertInstanceOf(RoleDTO::class, $result['editor']);
    }

    public function testGetNestedRoles(): void
    {
        $adminRole = new Role('admin');
        $editorRole = new Role('editor');
        $viewerRole = new Role('viewer');

        $this->manager->addRole($adminRole);
        $this->manager->addRole($editorRole);
        $this->manager->addRole($viewerRole);

        $this->manager->addChild($adminRole->getName(), $editorRole->getName());
        $this->manager->addChild($editorRole->getName(), $viewerRole->getName());

        $result = $this->service->getNestedRoles(new RoleDTO('admin'));

        $this->assertCount(1, $result);
        $this->assertArrayHasKey('viewer', $result);
        $this->assertArrayNotHasKey('editor', $result);
        $this->assertInstanceOf(RoleDTO::class, $result['viewer']);
    }

    public function testGetPermissionsByRole(): void
    {
        $role = new Role('admin');
        $authorRole = new Role('author');
        $permission = new Permission('create_post');
        $this->manager->addRole($role);
        $this->manager->addRole($authorRole);
        $this->manager->addPermission($permission);
        $this->manager->addChild($role->getName(), $authorRole->getName());
        $this->manager->addChild($role->getName(), $permission->getName());

        $result = $this->service->getPermissionsByRole(new RoleDTO('admin'));

        $this->assertArrayHasKey('create_post', $result);
        $this->assertInstanceOf(PermissionDTO::class, $result['create_post']);
    }

    public function testGetNestedPermissionsByRole(): void
    {
        $adminRole = new Role('admin');
        $editorRole = new Role('editor');
        $createPermission = new Permission('create_post');
        $editPermission = new Permission('edit_post');

        $this->manager->addRole($adminRole);
        $this->manager->addRole($editorRole);
        $this->manager->addPermission($createPermission);
        $this->manager->addPermission($editPermission);

        $this->manager->addChild($adminRole->getName(), $editorRole->getName());
        $this->manager->addChild($adminRole->getName(), $createPermission->getName());
        $this->manager->addChild($editorRole->getName(), $editPermission->getName());

        $result = $this->service->getNestedPermissionsByRole(new RoleDTO('admin'));

        $this->assertCount(1, $result);
        $this->assertArrayHasKey('edit_post', $result);
        $this->assertArrayNotHasKey('create_post', $result);
    }

    public function testHasChildrenWhenHasChildren(): void
    {
        $parentRole = new Role('admin');
        $childRole = new Role('editor');
        $this->manager->addRole($parentRole);
        $this->manager->addRole($childRole);
        $this->manager->addChild($parentRole->getName(), $childRole->getName());

        $this->assertTrue($this->service->hasChildren(new RoleDTO('admin')));
    }

    public function testHasChildrenWhenNoChildren(): void
    {
        $role = new Role('admin');
        $this->manager->addRole($role);

        $this->assertFalse($this->service->hasChildren(new RoleDTO('admin')));
    }

    public function testGetDefaultRoleNames(): void
    {
        $roles = ['admin', 'user'];
        $this->service->setDefaultRoles($roles);

        $result = $this->service->getDefaultRoleNames();

        $this->assertSame($roles, $result);
    }

    /**
     * @throws Exception
     */
    #[Override]
    protected function _before(): void
    {
        $this->manager = new Manager(
            $this->getItemStorage(),
            $this->getAssignmentsStorage(),
            $this->createMock(RuleFactoryInterface::class),
        );

        $this->service = new AccessRightsService(
            $this->manager,
            $this->getItemStorage(),
        );

        // Clear any default roles that might be set
        $this->manager->setDefaultRoleNames([]);
    }


    #[Override]
    protected function _after(): void
    {
        $this->getItemStorage()->clear();
        $this->getAssignmentsStorage()->clear();
    }

    private function initTempDir(): string
    {
        if (self::$tempDir === null) {
            self::$tempDir = sys_get_temp_dir() . '/test_' . uniqid('', true);
            mkdir(self::$tempDir, 0777, true);
            return self::$tempDir;
        }

        return self::$tempDir;
    }

    private function getItemStorage(): ItemsStorageInterface
    {
        $dir = $this->initTempDir();
        if (self::$storage === null) {
            self::$storage = new ItemsStorage($dir . '/items.php');
            return self::$storage;
        }
        return self::$storage;
    }

    private function getAssignmentsStorage(): AssignmentsStorageInterface
    {
        $dir = $this->initTempDir();
        if (self::$assignmentsStorage === null) {
            self::$assignmentsStorage = new AssignmentsStorage($dir . '/assignments.php');
        }
        return self::$assignmentsStorage;
    }
}
