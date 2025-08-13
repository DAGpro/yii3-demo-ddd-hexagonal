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
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Yiisoft\Rbac\ItemsStorageInterface;
use Yiisoft\Rbac\Manager;
use Yiisoft\Rbac\ManagerInterface;
use Yiisoft\Rbac\Permission;
use Yiisoft\Rbac\Php\AssignmentsStorage;
use Yiisoft\Rbac\Php\ItemsStorage;
use Yiisoft\Rbac\Role;
use Yiisoft\Rbac\RuleFactoryInterface;

#[CoversClass(AssignAccessService::class)]
final class AssignAccessServiceTest extends TestCase
{
    private AssignAccessService $assignService;
    private ManagerInterface $manager;
    private AccessRightsServiceInterface $accessRightsService;
    private ItemsStorageInterface $storage;
    private AssignmentsServiceInterface $assignmentsService;
    private AssignmentsStorage $assignmentsStorage;
    private string $tempDir;

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

        $this->manager->addPermission(new Permission('create_post')->withDescription('Create Post'));

        $this->assignService->assignPermission($permissionDTO, $userId);
        $this->assertTrue($this->assignmentsService->userHasPermission($userId, 'create_post'));
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
        $property->setAccessible(true);
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
    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/test_' . uniqid('', true);
        mkdir($this->tempDir, 0777, true);

        $this->storage = new ItemsStorage($this->tempDir . '/items.php');
        $this->assignmentsStorage = new AssignmentsStorage($this->tempDir . '/assignments.php');
        $this->manager = new Manager(
            $this->storage,
            $this->assignmentsStorage,
            $this->createMock(RuleFactoryInterface::class),
            true,
        );

        $this->accessRightsService = new AccessRightsService(
            $this->manager,
            $this->storage,
        );

        $this->assignmentsService = new AssignmentsService(
            $this->assignmentsStorage,
            $this->accessRightsService,
            $this->createMock(UserQueryServiceInterface::class),
            $this->manager,
        );

        $this->assignService = new AssignAccessService(
            $this->manager,
            $this->accessRightsService,
            $this->assignmentsStorage,
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
