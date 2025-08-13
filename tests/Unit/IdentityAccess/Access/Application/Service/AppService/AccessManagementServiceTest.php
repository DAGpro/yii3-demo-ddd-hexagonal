<?php

declare(strict_types=1);

namespace App\Tests\Unit\IdentityAccess\Access\Application\Service\AppService;

use App\IdentityAccess\Access\Application\Service\AccessRightsServiceInterface;
use App\IdentityAccess\Access\Application\Service\AppService\AccessManagementService;
use App\IdentityAccess\Access\Application\Service\AppService\AccessRightsService;
use App\IdentityAccess\Access\Application\Service\AppService\AssignmentsService;
use App\IdentityAccess\Access\Application\Service\AssignmentsServiceInterface;
use App\IdentityAccess\Access\Application\Service\PermissionDTO;
use App\IdentityAccess\Access\Application\Service\RoleDTO;
use App\IdentityAccess\Access\Domain\Exception\AssignedItemException;
use App\IdentityAccess\Access\Domain\Exception\ExistItemException;
use App\IdentityAccess\Access\Domain\Exception\NotExistItemException;
use App\IdentityAccess\User\Application\Service\UserQueryServiceInterface;
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

#[CoversClass(AccessManagementService::class)]
final class AccessManagementServiceTest extends TestCase
{
    private AccessManagementService $service;
    private Manager $manager;
    private MockObject|ItemsStorageInterface $storage;
    private MockObject|AccessRightsServiceInterface $accessRightsService;
    private AssignmentsServiceInterface $assignmentsService;

    private string $tempDir;

    /**
     * @throws ExistItemException
     */
    public function testAddRole(): void
    {
        $roleDTO = new RoleDTO('admin');

        $this->service->addRole($roleDTO);

        $this->assertInstanceOf(Role::class, $this->manager->getRole('admin'));
        $this->assertSame('admin', $this->manager->getRole('admin')->getName());
    }

    public function testAddRoleThrowsExceptionIfRoleExists(): void
    {
        $this->expectException(ExistItemException::class);

        $roleDTO = new RoleDTO('admin');

        $this->service->addRole($roleDTO);
        $this->service->addRole($roleDTO);
    }

    /**
     * @throws NotExistItemException
     * @throws AssignedItemException
     * @throws ExistItemException
     */
    public function testRemoveRole(): void
    {
        $roleDTO = new RoleDTO('admin', 'Administrator');

        $this->service->addRole($roleDTO);
        $this->assertInstanceOf(Role::class, $this->manager->getRole('admin'));
        $this->service->removeRole($roleDTO);
        $this->assertNull($this->manager->getRole('admin'));
    }

    /**
     * @throws AssignedItemException
     * @throws ExistItemException
     */
    public function testRemoveRoleThrowsExceptionIfRoleNotExists(): void
    {
        $this->expectException(NotExistItemException::class);

        $roleDTO = new RoleDTO('admin', 'Administrator');

        $this->service->removeRole($roleDTO);
    }

    /**
     * @throws ExistItemException
     */
    public function testAddPermission(): void
    {
        $permissionDTO = new PermissionDTO('create_post', 'Create posts');

        $this->service->addPermission($permissionDTO);
        $this->assertInstanceOf(Permission::class, $this->manager->getPermission('create_post'));
        $this->assertSame('create_post', $this->manager->getPermission('create_post')->getName());
    }

    public function testAddPermissionThrowsExceptionIfPermissionExists(): void
    {
        $this->expectException(ExistItemException::class);

        $permissionDTO = new PermissionDTO('create_post', 'Create posts');

        $this->service->addPermission($permissionDTO);
        $this->service->addPermission($permissionDTO);
    }

    /**
     * @throws NotExistItemException
     * @throws AssignedItemException
     * @throws ExistItemException
     */
    public function testRemovePermission(): void
    {
        $permissionDTO = new PermissionDTO('create_post', 'Create posts');

        $this->service->addPermission($permissionDTO);

        $this->service->removePermission($permissionDTO);
        $this->assertNull($this->manager->getPermission('create_post'));
    }

    /**
     * @throws AssignedItemException
     */
    public function testRemovePermissionThrowsExceptionIfPermissionNotExists(): void
    {
        $this->expectException(NotExistItemException::class);

        $permissionDTO = new PermissionDTO('create_post', 'Create posts');

        $this->service->removePermission($permissionDTO);
        $this->assertNull($this->manager->getPermission('create_post'));
    }

    /**
     * @throws NotExistItemException
     * @throws ExistItemException
     */
    public function testAddChildRole()
    {
        $roleDTO = new RoleDTO('admin', 'Administrator');
        $childRoleDTO = new RoleDTO('user', 'User');

        $this->service->addRole($roleDTO);
        $this->service->addRole($childRoleDTO);
        $this->service->addChildRole($roleDTO, $childRoleDTO);
        $childRoles = $this->manager->getChildRoles('admin');
        $this->assertArrayHasKey('user', $childRoles);
    }

    /**
     * @throws Exception
     */
    #[Override]
    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/test_' . uniqid('', true);
        mkdir($this->tempDir, 0777, true);

        $assignmentsStorage = new AssignmentsStorage($this->tempDir . '/assignments.php');
        $this->manager = new Manager(
            $this->storage = new ItemsStorage($this->tempDir . '/items.php'),
            $assignmentsStorage,
            $this->createMock(RuleFactoryInterface::class),
        );

        $this->accessRightsService = new AccessRightsService(
            $this->manager,
            $this->storage,
        );

        $this->assignmentsService = new AssignmentsService(
            $assignmentsStorage,
            $this->accessRightsService,
            $this->createMock(UserQueryServiceInterface::class),
            $this->manager,
        );

        $this->service = new AccessManagementService(
            $this->manager,
            $this->storage,
            $this->accessRightsService,
            $this->assignmentsService,
        );
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
