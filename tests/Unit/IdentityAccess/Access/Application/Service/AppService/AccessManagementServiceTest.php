<?php

declare(strict_types=1);

namespace App\Tests\Unit\IdentityAccess\Access\Application\Service\AppService;

use App\IdentityAccess\Access\Domain\Exception\AssignedItemException;
use App\IdentityAccess\Access\Domain\Exception\ExistItemException;
use App\IdentityAccess\Access\Domain\Exception\NotExistItemException;
use App\IdentityAccess\Access\Slice\Service\AccessRightsServiceInterface;
use App\IdentityAccess\Access\Slice\Service\AppService\AccessManagementService;
use App\IdentityAccess\Access\Slice\Service\AppService\AccessRightsService;
use App\IdentityAccess\Access\Slice\Service\AppService\AssignmentsService;
use App\IdentityAccess\Access\Slice\Service\AssignmentsServiceInterface;
use App\IdentityAccess\Access\Slice\Service\PermissionDTO;
use App\IdentityAccess\Access\Slice\Service\RoleDTO;
use App\IdentityAccess\User\Slice\User\Service\UserQueryServiceInterface;
use App\Tests\UnitTester;
use Codeception\Test\Unit;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use Yiisoft\Rbac\AssignmentsStorageInterface;
use Yiisoft\Rbac\ItemsStorageInterface;
use Yiisoft\Rbac\Manager;
use Yiisoft\Rbac\Permission;
use Yiisoft\Rbac\Php\AssignmentsStorage;
use Yiisoft\Rbac\Php\ItemsStorage;
use Yiisoft\Rbac\Role;
use Yiisoft\Rbac\RuleFactoryInterface;

#[CoversClass(AccessManagementService::class)]
final class AccessManagementServiceTest extends Unit
{
    private static ?ItemsStorageInterface $storage = null;

    private static ?AssignmentsStorageInterface $assignmentsStorage = null;

    private static ?string $tempDir = null;

    protected UnitTester $tester;

    private AccessManagementService $service;

    private Manager $manager;

    private AccessRightsServiceInterface $accessRightsService;

    private AssignmentsServiceInterface $assignmentsService;

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
     * @throws AssignedItemException
     * @throws NotExistItemException
     * @throws ExistItemException
     */
    public function testRemoveRole(): void
    {
        $roleDTO = new RoleDTO('admin');
        $this->service->addRole($roleDTO);
        $this->assertTrue($this->accessRightsService->existRole($roleDTO->getName()));

        $this->service->removeRole($roleDTO);
        $this->assertFalse($this->accessRightsService->existRole($roleDTO->getName()));
    }

    /**
     * @throws AssignedItemException
     */
    public function testRemoveRoleThrowsExceptionIfRoleNotExists(): void
    {
        $this->expectException(NotExistItemException::class);
        $this->expectExceptionMessage("Role 'nonexistent' not exists!");

        $roleDTO = new RoleDTO('nonexistent');
        $this->service->removeRole($roleDTO);
    }

    /**
     * @throws NotExistItemException
     * @throws ExistItemException
     */
    public function testRemoveRoleThrowsExceptionIfAssignedToUser(): void
    {
        $this->expectException(AssignedItemException::class);
        $this->expectExceptionMessage('This role is assigned to users.');

        $roleDTO = new RoleDTO('admin');
        $this->service->addRole($roleDTO);
        $this->manager->assign($roleDTO->getName(), '1');

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
    public function testRemovePermissionThrowsExceptionIfAssignedToUser(): void
    {
        $this->expectException(AssignedItemException::class);
        $this->expectExceptionMessage('This permission is assigned to users.');

        $permissionDTO = new PermissionDTO('create_post');
        $this->service->addPermission($permissionDTO);

        // Assign permission to a user
        $this->manager->assign('create_post', '1');

        $this->service->removePermission($permissionDTO);
    }

    /**
     * @throws ExistItemException
     * @throws NotExistItemException
     */
    public function testAddChildPermission(): void
    {
        $roleDTO = new RoleDTO('admin');
        $permissionDTO = new PermissionDTO('create_post', 'Create posts');

        $this->service->addRole($roleDTO);
        $this->service->addPermission($permissionDTO);

        $this->service->addChildPermission($roleDTO, $permissionDTO);

        $this->assertTrue($this->service->hasChildPermission($roleDTO, $permissionDTO));
    }

    /**
     * @throws ExistItemException
     * @throws NotExistItemException
     */
    public function testRemoveChildRole(): void
    {
        $parentRole = new RoleDTO('admin');
        $childRole = new RoleDTO('editor');

        $this->service->addRole($parentRole);
        $this->service->addRole($childRole);
        $this->service->addChildRole($parentRole, $childRole);

        $this->assertTrue($this->service->hasChildRole($parentRole, $childRole));

        $this->service->removeChildRole($parentRole, $childRole);

        $this->assertFalse($this->service->hasChildRole($parentRole, $childRole));
    }

    /**
     * @throws ExistItemException
     * @throws NotExistItemException
     */
    public function testRemoveChildPermission(): void
    {
        $roleDTO = new RoleDTO('admin');
        $permissionDTO = new PermissionDTO('create_post', 'Create posts');

        $this->service->addRole($roleDTO);
        $this->service->addPermission($permissionDTO);
        $this->service->addChildPermission($roleDTO, $permissionDTO);

        $this->assertTrue($this->service->hasChildPermission($roleDTO, $permissionDTO));

        $this->service->removeChildPermission($roleDTO, $permissionDTO);

        $this->assertFalse($this->service->hasChildPermission($roleDTO, $permissionDTO));
    }

    /**
     * @throws ExistItemException
     * @throws NotExistItemException
     */
    public function testRemoveChildren(): void
    {
        $parentRole = new RoleDTO('admin');
        $childRole = new RoleDTO('editor');
        $permissionDTO = new PermissionDTO('create_post', 'Create posts');

        $this->service->addRole($parentRole);
        $this->service->addRole($childRole);
        $this->service->addPermission($permissionDTO);

        $this->service->addChildRole($parentRole, $childRole);
        $this->service->addChildPermission($parentRole, $permissionDTO);

        $this->assertTrue($this->service->hasChildRole($parentRole, $childRole));
        $this->assertTrue($this->service->hasChildPermission($parentRole, $permissionDTO));

        $this->service->removeChildren($parentRole);

        $this->assertFalse($this->service->hasChildRole($parentRole, $childRole));
        $this->assertFalse($this->service->hasChildPermission($parentRole, $permissionDTO));
    }

    /**
     * @throws ExistItemException
     */
    public function testClearAccessRights(): void
    {
        $roleDTO = new RoleDTO('admin');
        $permissionDTO = new PermissionDTO('create_post', 'Create posts');

        $this->service->addRole($roleDTO);
        $this->service->addPermission($permissionDTO);

        $this->assertNotNull($this->manager->getRole('admin'));
        $this->assertNotNull($this->manager->getPermission('create_post'));

        $this->service->clearAccessRights();

        $this->assertNull($this->manager->getRole('admin'));
        $this->assertNull($this->manager->getPermission('create_post'));
    }

    /**
     * @throws ExistItemException
     * @throws NotExistItemException
     * @throws AssignedItemException
     */
    public function testRemovePermissionRemovesFromRoles(): void
    {
        $roleDTO = new RoleDTO('admin');
        $permissionDTO = new PermissionDTO('create_post', 'Create posts');

        $this->service->addRole($roleDTO);
        $this->service->addPermission($permissionDTO);
        $this->service->addChildPermission($roleDTO, $permissionDTO);

        $this->assertTrue($this->service->hasChildPermission($roleDTO, $permissionDTO));

        $this->service->removePermission($permissionDTO);

        $this->assertFalse($this->service->hasChildPermission($roleDTO, $permissionDTO));
    }

    /**
     * @throws ExistItemException
     * @throws NotExistItemException
     * @throws AssignedItemException
     */
    public function testRemoveRoleRemovesChildren(): void
    {
        $parentRole = new RoleDTO('admin');
        $childRole = new RoleDTO('editor');

        $this->service->addRole($parentRole);
        $this->service->addRole($childRole);
        $this->service->addChildRole($parentRole, $childRole);

        $this->assertTrue($this->service->hasChildRole($parentRole, $childRole));

        $this->service->removeRole($parentRole);

        $this->assertNull($this->manager->getRole('admin'));
        $this->assertFalse($this->service->hasChildRole($parentRole, $childRole));
    }

    /**
     * @throws ExistItemException
     */
    public function testAddChildRoleThrowsExceptionIfParentRoleNotExists(): void
    {
        $this->expectException(NotExistItemException::class);
        $this->expectExceptionMessage("Role 'admin' not exists!");

        $parentRole = new RoleDTO('admin');
        $childRole = new RoleDTO('editor');

        // Only add child role, parent doesn't exist
        $this->service->addRole($childRole);

        $this->service->addChildRole($parentRole, $childRole);
    }

    /**
     * @throws ExistItemException
     */
    public function testAddChildRoleThrowsExceptionIfChildRoleNotExists(): void
    {
        $this->expectException(NotExistItemException::class);
        $this->expectExceptionMessage("Role 'editor' not exists!");

        $parentRole = new RoleDTO('admin');
        $childRole = new RoleDTO('editor');

        // Only add parent role, child doesn't exist
        $this->service->addRole($parentRole);

        $this->service->addChildRole($parentRole, $childRole);
    }

    public function testAddChildRoleThrowsExceptionForCircularReference(): void
    {
        $this->expectException(ExistItemException::class);
        $this->expectExceptionMessage('Unable to add child role!');

        $adminRole = new RoleDTO('admin');
        $editorRole = new RoleDTO('editor');
        $managerRole = new RoleDTO('manager');

        // Create all roles
        $this->service->addRole($adminRole);
        $this->service->addRole($editorRole);
        $this->service->addRole($managerRole);

        // Set up hierarchy: admin -> editor -> manager
        $this->service->addChildRole($adminRole, $editorRole);
        $this->service->addChildRole($editorRole, $managerRole);

        // Try to create a circular reference: manager -> admin
        $this->service->addChildRole($managerRole, $adminRole);
    }

    /**
     * @throws ExistItemException
     * @throws NotExistItemException
     */
    public function testAddChildRoleThrowsExceptionForExistingChild(): void
    {
        $this->expectException(ExistItemException::class);
        $this->expectExceptionMessage('Unable to add child role!');

        $adminRole = new RoleDTO('admin');
        $editorRole = new RoleDTO('editor');

        // Create roles
        $this->service->addRole($adminRole);
        $this->service->addRole($editorRole);

        // Add editor as a child of admin
        $this->service->addChildRole($adminRole, $editorRole);

        // Try to add the same child again
        $this->service->addChildRole($adminRole, $editorRole);
    }

    /**
     * @throws ExistItemException
     */
    public function testAddChildPermissionThrowsExceptionIfRoleNotExists(): void
    {
        $this->expectException(NotExistItemException::class);
        $this->expectExceptionMessage("Role 'admin' not exists!");

        $roleDTO = new RoleDTO('admin');
        $permissionDTO = new PermissionDTO('create_post');

        // Only add permission, role doesn't exist
        $this->service->addPermission($permissionDTO);

        $this->service->addChildPermission($roleDTO, $permissionDTO);
    }

    /**
     * @throws ExistItemException
     */
    public function testAddChildPermissionThrowsExceptionIfPermissionNotExists(): void
    {
        $this->expectException(NotExistItemException::class);
        $this->expectExceptionMessage("Permission 'create_post' not exists!");

        $roleDTO = new RoleDTO('admin');
        $permissionDTO = new PermissionDTO('create_post');

        // Only add role, permission doesn't exist
        $this->service->addRole($roleDTO);

        $this->service->addChildPermission($roleDTO, $permissionDTO);
    }

    /**
     * @throws ExistItemException
     * @throws NotExistItemException
     */
    public function testAddChildPermissionThrowsExceptionForExistingChild(): void
    {
        $this->expectException(ExistItemException::class);
        $this->expectExceptionMessage('Unable to add child permission!');

        $adminRole = new RoleDTO('admin');
        $createPostPermission = new PermissionDTO('create_post');

        // Create role and permission
        $this->service->addRole($adminRole);
        $this->service->addPermission($createPostPermission);

        // Add permission as a child of role
        $this->service->addChildPermission($adminRole, $createPostPermission);

        // Try to add the same child permission again
        $this->service->addChildPermission($adminRole, $createPostPermission);
    }

    /**
     * @throws ExistItemException
     * @throws NotExistItemException
     */
    public function testAddChildPermissionThrowsExceptionForSelfReference(): void
    {
        $this->expectException(ExistItemException::class);
        $this->expectExceptionMessage('Unable to add child permission!');

        $adminRole = new RoleDTO('admin');
        $adminPermission = new PermissionDTO('admin_permission');

        // Create role and permission
        $this->service->addRole($adminRole);
        $this->service->addPermission($adminPermission);

        // Add permission to role
        $this->service->addChildPermission($adminRole, $adminPermission);

        // Try to add permission as a child of itself (which shouldn't be possible)
        $this->service->addChildPermission($adminRole, $adminPermission);
    }

    /**
     * @throws ExistItemException
     */
    public function testRemoveChildRoleThrowsExceptionIfParentRoleNotExists(): void
    {
        $this->expectException(NotExistItemException::class);
        $this->expectExceptionMessage("Role 'admin' not exists!");

        $parentRole = new RoleDTO('admin');
        $childRole = new RoleDTO('editor');

        // Only add child role, parent doesn't exist
        $this->service->addRole($childRole);

        $this->service->removeChildRole($parentRole, $childRole);
    }

    /**
     * @throws ExistItemException
     */
    public function testRemoveChildRoleThrowsExceptionIfChildRoleNotExists(): void
    {
        $this->expectException(NotExistItemException::class);
        $this->expectExceptionMessage("Role 'editor' not exists!");

        $parentRole = new RoleDTO('admin');
        $childRole = new RoleDTO('editor');

        // Only add parent role, child doesn't exist
        $this->service->addRole($parentRole);

        $this->service->removeChildRole($parentRole, $childRole);
    }

    /**
     * @throws ExistItemException
     * @throws NotExistItemException
     */
    public function testRemoveChildRoleThrowsExceptionIfNotAChild(): void
    {
        $this->expectException(NotExistItemException::class);
        $this->expectExceptionMessage('The parent role does not have this child role!');

        $parentRole = new RoleDTO('admin');
        $childRole = new RoleDTO('editor');

        $this->service->addRole($parentRole);
        $this->service->addRole($childRole);

        // Try to remove non-existent child relationship
        $this->service->removeChildRole($parentRole, $childRole);
    }

    /**
     * @throws ExistItemException
     */
    public function testRemoveChildPermissionThrowsExceptionIfRoleNotExists(): void
    {
        $this->expectException(NotExistItemException::class);
        $this->expectExceptionMessage("Role 'admin' not exists!");

        $roleDTO = new RoleDTO('admin');
        $permissionDTO = new PermissionDTO('create_post');

        // Only add permission, role doesn't exist
        $this->service->addPermission($permissionDTO);

        $this->service->removeChildPermission($roleDTO, $permissionDTO);
    }

    /**
     * @throws ExistItemException
     */
    public function testRemoveChildPermissionThrowsExceptionIfPermissionNotExists(): void
    {
        $this->expectException(NotExistItemException::class);
        $this->expectExceptionMessage("Permission 'create_post' not exists!");

        $roleDTO = new RoleDTO('admin');
        $permissionDTO = new PermissionDTO('create_post');

        // Only add role, permission doesn't exist
        $this->service->addRole($roleDTO);

        $this->service->removeChildPermission($roleDTO, $permissionDTO);
    }

    /**
     * @throws ExistItemException
     * @throws NotExistItemException
     */
    public function testRemoveChildPermissionThrowsExceptionIfNotAChild(): void
    {
        $this->expectException(NotExistItemException::class);
        $this->expectExceptionMessage('The parent role does not have this child permission!');

        $roleDTO = new RoleDTO('admin');
        $permissionDTO = new PermissionDTO('create_post');

        $this->service->addRole($roleDTO);
        $this->service->addPermission($permissionDTO);

        // Try to remove non-existent child permission relationship
        $this->service->removeChildPermission($roleDTO, $permissionDTO);
    }

    /**
     * @throws NotExistItemException
     * @throws ExistItemException
     */
    public function testAddChildRole(): void
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
    protected function _before(): void
    {
        $assignmentsStorage = $this->getAssignmentsStorage();
        $this->manager = new Manager(
            $this->getItemStorage(),
            $assignmentsStorage,
            $this->createMock(RuleFactoryInterface::class),
            true,
        );

        $this->accessRightsService = new AccessRightsService(
            $this->manager,
            $this->getItemStorage(),
        );

        $this->assignmentsService = new AssignmentsService(
            $assignmentsStorage,
            $this->accessRightsService,
            $this->createMock(UserQueryServiceInterface::class),
            $this->manager,
        );

        $this->service = new AccessManagementService(
            $this->manager,
            $this->getItemStorage(),
            $this->accessRightsService,
            $this->assignmentsService,
        );
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
