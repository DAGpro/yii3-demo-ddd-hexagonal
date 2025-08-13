<?php

declare(strict_types=1);

namespace App\Tests\Unit\IdentityAccess\Access\Application\Service\AppService;

use App\IdentityAccess\Access\Application\Service\AccessRightsServiceInterface;
use App\IdentityAccess\Access\Application\Service\AppService\AccessRightsService;
use App\IdentityAccess\Access\Application\Service\PermissionDTO;
use App\IdentityAccess\Access\Application\Service\RoleDTO;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Yiisoft\Rbac\ItemsStorageInterface;
use Yiisoft\Rbac\Manager;
use Yiisoft\Rbac\Permission;
use Yiisoft\Rbac\Php\AssignmentsStorage;
use Yiisoft\Rbac\Php\ItemsStorage;
use Yiisoft\Rbac\Role;
use Yiisoft\Rbac\RuleFactoryInterface;

#[CoversClass(AccessRightsService::class)]
final class AccessRightsServiceTest extends TestCase
{
    private AccessRightsService $service;
    private MockObject|Manager $manager;
    private MockObject|ItemsStorageInterface $storage;
    private string $tempDir;

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

        $this->assertIsArray($result);
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

    public function testGetPermissionByNameWhenPermissionExists(): void
    {
        $permissionName = 'create_post';
        $permission = new Permission($permissionName);

        $this->manager->addPermission($permission);

        $result = $this->service->getPermissionByName($permissionName);

        $this->assertInstanceOf(PermissionDTO::class, $result);
        $this->assertSame($permissionName, $result->getName());
    }

    public function testSetDefaultRoles(): void
    {
        $roles = ['admin', 'user'];

        $result = $this->service->setDefaultRoles($roles);

        $this->assertInstanceOf(AccessRightsServiceInterface::class, $result);
        $this->assertSame($this->service, $result);
        $this->assertSame($roles, $this->service->getDefaultRoleNames());
    }

    /**
     * @throws Exception
     */
    #[Override]
    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/test_' . uniqid('', true);
        mkdir($this->tempDir, 0777, true);

        $this->manager = new Manager(
            $this->storage = new ItemsStorage($this->tempDir . '/items.php'),
            new AssignmentsStorage($this->tempDir . '/assignments.php'),
            $this->createMock(RuleFactoryInterface::class),
        );

        $this->service = new AccessRightsService($this->manager, $this->storage);
    }

    #[Override]
    protected function tearDown(): void
    {
        $this->removeDirectory($this->tempDir);
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
}
