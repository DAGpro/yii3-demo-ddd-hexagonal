<?php

declare(strict_types=1);

namespace App\Tests\Unit\IdentityAccess\Access\Application\Service\AppService;

use App\IdentityAccess\Access\Application\Service\AccessRightsServiceInterface;
use App\IdentityAccess\Access\Application\Service\AppService\AccessRightsService;
use App\IdentityAccess\Access\Application\Service\AppService\AssignAccessService;
use App\IdentityAccess\Access\Application\Service\AppService\AssignmentsService;
use App\IdentityAccess\Access\Application\Service\AssignmentsServiceInterface;
use App\IdentityAccess\Access\Application\Service\PermissionDTO;
use App\IdentityAccess\Access\Application\Service\RoleDTO;
use App\IdentityAccess\Access\Domain\Exception\AssignedItemException;
use App\IdentityAccess\Access\Domain\Exception\NotExistItemException;
use App\IdentityAccess\User\Application\Service\UserQueryServiceInterface;
use App\IdentityAccess\User\Domain\User;
use App\Tests\UnitTester;
use Codeception\Test\Unit;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use ReflectionClass;
use Yiisoft\Rbac\AssignmentsStorageInterface;
use Yiisoft\Rbac\ItemsStorageInterface;
use Yiisoft\Rbac\Manager;
use Yiisoft\Rbac\ManagerInterface;
use Yiisoft\Rbac\Permission;
use Yiisoft\Rbac\Php\AssignmentsStorage;
use Yiisoft\Rbac\Php\ItemsStorage;
use Yiisoft\Rbac\Role;
use Yiisoft\Rbac\RuleFactoryInterface;

#[CoversClass(AssignAccessService::class)]
final class AssignAccessServiceTest extends Unit
{
    private static ?ItemsStorageInterface $storage = null;

    private static ?AssignmentsStorageInterface $assignmentsStorage = null;

    private static ?string $tempDir = null;

    protected UnitTester $tester;

    private AssignAccessService $assignService;

    private ManagerInterface $manager;

    private AccessRightsServiceInterface $accessRightsService;

    private AssignmentsServiceInterface $assignmentsService;

    /**
     * @throws AssignedItemException
     * @throws NotExistItemException
     */
    public function testAssignRoleSuccessfully(): void
    {
        $roleDTO = new RoleDTO('admin', 'Administrator');
        $userId = 1;

        $this->manager->addRole(new Role('admin')->withDescription('Administrator'));

        $this->assignService->assignRole($roleDTO, $userId);
        $this->assertTrue($this->assignmentsService->userHasRole($userId, 'admin'));
    }

    /**
     * @throws AssignedItemException
     */
    public function testAssignRoleThrowsExceptionWhenRoleDoesNotExist(): void
    {
        $roleDTO = new RoleDTO('nonexistent', 'Nonexistent Role');
        $userId = 1;

        $this->expectException(NotExistItemException::class);
        $this->expectExceptionMessage('This role does not exist!');

        $this->assignService->assignRole($roleDTO, $userId);
    }

    /**
     * @throws NotExistItemException
     * @throws AssignedItemException
     */
    public function testAssignRoleThrowsExceptionWhenRoleAlreadyAssigned(): void
    {
        $roleDTO = new RoleDTO('admin', 'Administrator');
        $userId = 1;

        $this->manager->addRole(new Role('admin')->withDescription('Administrator'));
        $this->assignService->assignRole($roleDTO, $userId);

        $this->expectException(AssignedItemException::class);
        $this->expectExceptionMessage('The role has already been assigned to the user!');

        $this->assignService->assignRole($roleDTO, $userId);
    }

    /**
     * @throws NotExistItemException
     * @throws AssignedItemException
     */
    public function testAssignPermissionSuccessfully(): void
    {
        $permissionDTO = new PermissionDTO('create_post', 'Create Post');
        $userId = 1;

        $this->manager->addPermission(
            new Permission('create_post')
                ->withDescription('Create Post'),
        );

        // Test assigning permission
        $this->assignService->assignPermission($permissionDTO, $userId);
        $this->assertTrue($this->assignmentsService->userHasPermission($userId, 'create_post'));

        // Verify the permission is in user's assignments
        $user = new User('test', 'password');
        $reflection = new ReflectionClass($user);
        $property = $reflection->getProperty('id');
        $property->setValue($user, $userId);

        $userAssignments = $this->assignmentsService->getUserAssignments($user);
        $this->assertTrue($userAssignments->existPermissions());
        $this->assertArrayHasKey('create_post', $userAssignments->getPermissions());
    }

    /**
     * @throws AssignedItemException
     */
    public function testAssignPermissionThrowsExceptionWhenPermissionDoesNotExist(): void
    {
        $permissionDTO = new PermissionDTO('nonexistent', 'Nonexistent Permission');
        $userId = 1;

        $this->expectException(NotExistItemException::class);
        $this->expectExceptionMessage('This permission does not exist!');

        $this->assignService->assignPermission($permissionDTO, $userId);
    }

    /**
     * @throws NotExistItemException
     * @throws AssignedItemException
     */
    public function testAssignPermissionThrowsExceptionWhenAlreadyAssigned(): void
    {
        $permissionDTO = new PermissionDTO('create_post', 'Create Post');
        $userId = 1;

        $this->manager->addPermission(new Permission('create_post'));
        $this->assignService->assignPermission($permissionDTO, $userId);

        $this->expectException(AssignedItemException::class);
        $this->expectExceptionMessage('The permission has already been assigned to the user!');

        $this->assignService->assignPermission($permissionDTO, $userId);
    }

    /**
     * @throws NotExistItemException
     * @throws AssignedItemException
     */
    public function testRevokePermissionSuccessfully(): void
    {
        $permissionDTO = new PermissionDTO('create_post', 'Create Post');
        $userId = 1;

        // Add permission and assign it to user
        $this->manager->addPermission(new Permission('create_post'));
        $this->assignService->assignPermission($permissionDTO, $userId);
        $this->assertTrue($this->assignmentsService->userHasPermission($userId, 'create_post'));

        // Revoke the permission
        $this->assignService->revokePermission($permissionDTO, $userId);

        // Verify permission was revoked
        $this->assertFalse($this->assignmentsService->userHasPermission($userId, 'create_post'));

        $user = new User('test', 'password');
        $reflection = new ReflectionClass($user);
        $property = $reflection->getProperty('id');
        $property->setValue($user, $userId);

        $userAssignments = $this->assignmentsService->getUserAssignments($user);
        $this->assertEmpty($userAssignments->getPermissions());
    }

    public function testRevokePermissionThrowsExceptionWhenNotAssigned(): void
    {
        $permissionDTO = new PermissionDTO('create_post', 'Create Post');
        $userId = 1;

        // Add permission but don't assign it
        $this->manager->addPermission(new Permission('create_post'));

        $this->expectException(AssignedItemException::class);
        $this->expectExceptionMessage('The permission was not previously assigned to the user!');

        $this->assignService->revokePermission($permissionDTO, $userId);
    }

    public function testRevokePermissionThrowsExceptionWhenPermissionDoesNotExist(): void
    {
        $permissionDTO = new PermissionDTO('nonexistent', 'Nonexistent Permission');
        $userId = 1;

        $this->expectException(AssignedItemException::class);
        $this->expectExceptionMessage('The permission was not previously assigned to the user!');

        $this->assignService->revokePermission($permissionDTO, $userId);
    }

    /**
     * @throws AssignedItemException
     * @throws NotExistItemException
     */
    public function testRevokeRoleSuccessfully(): void
    {
        $roleDTO = new RoleDTO('admin', 'Administrator');
        $userId = 1;
        $this->manager->addRole(new Role('admin')->withDescription('Administrator'));
        $this->assignService->assignRole($roleDTO, $userId);

        $this->assignService->revokeRole($roleDTO, $userId);
        $this->assertFalse($this->assignmentsService->userHasRole($userId, 'admin'));
    }

    public function testRevokeRoleThrowsExceptionWhenRoleNotAssigned(): void
    {
        $roleDTO = new RoleDTO('admin', 'Administrator');
        $userId = 1;

        $this->expectException(AssignedItemException::class);
        $this->expectExceptionMessage('The role was not previously assigned to the user!');

        $this->assignService->revokeRole($roleDTO, $userId);
    }

    /**
     * @throws NotExistItemException
     * @throws AssignedItemException
     */
    public function testRevokeAll(): void
    {
        $roleDTO = new RoleDTO('admin', 'Administrator');
        $userId = 1;
        $this->manager->addRole(new Role('admin')->withDescription('Administrator'));
        $this->assignService->assignRole($roleDTO, $userId);

        $this->assertTrue($this->assignmentsService->userHasRole($userId, 'admin'));
        $this->assignService->revokeAll($userId);

        $user = new User('login', 'password');
        $reflection = new ReflectionClass($user);
        $property = $reflection->getProperty('id');
        $property->setValue($user, 1);

        $userAssignments = $this->assignmentsService->getUserAssignments($user);
        $this->assertFalse($userAssignments->existRoles());
        $this->assertFalse($userAssignments->existPermissions());
    }

    /**
     * @throws NotExistItemException
     * @throws AssignedItemException
     */
    public function testClearAssignments(): void
    {
        $this->manager->addRole(new Role('admin')->withDescription('Administrator'));
        $this->manager->addRole(new Role('admin2')->withDescription('Administrator2'));
        $this->manager->addPermission(new Permission('create_post')->withDescription('Create Post'));
        $this->manager->addPermission(new Permission('create_post2')->withDescription('Create Post2'));
        $this->assignService->assignRole(new RoleDTO('admin', 'Administrator'), 1);
        $this->assignService->assignRole(new RoleDTO('admin2', 'Administrator2'), 2);
        $this->assignService->assignPermission(new PermissionDTO('create_post', 'Create Post'), 1);
        $this->assignService->assignPermission(new PermissionDTO('create_post2', 'Create Post2'), 1);

        $this->assignService->clearAssignments();
        $this->assertEquals([], $this->assignmentsService->getAssignments());
    }

    /**
     * @throws Exception
     */
    #[Override]
    protected function _before(): void
    {
        $this->getItemStorage();
        $this->getAssignmentsStorage();

        $this->manager = new Manager(
            $this->getItemStorage(),
            $this->getAssignmentsStorage(),
            $this->createMock(RuleFactoryInterface::class),
            true,
        );

        $this->accessRightsService = new AccessRightsService(
            $this->manager,
            $this->getItemStorage(),
        );

        $this->assignmentsService = new AssignmentsService(
            $this->getAssignmentsStorage(),
            $this->accessRightsService,
            $this->createMock(UserQueryServiceInterface::class),
            $this->manager,
        );

        $this->assignService = new AssignAccessService(
            $this->manager,
            $this->accessRightsService,
            $this->getAssignmentsStorage(),
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
