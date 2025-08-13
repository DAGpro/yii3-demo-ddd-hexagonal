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
use App\IdentityAccess\Access\Application\Service\UserAssignmentsDTO;
use App\IdentityAccess\User\Application\Service\UserQueryServiceInterface;
use App\IdentityAccess\User\Domain\User;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Yiisoft\Rbac\AssignmentsStorageInterface;
use Yiisoft\Rbac\Manager;
use Yiisoft\Rbac\Permission;
use Yiisoft\Rbac\Php\AssignmentsStorage;
use Yiisoft\Rbac\Php\ItemsStorage;
use Yiisoft\Rbac\Role;
use Yiisoft\Rbac\RuleFactoryInterface;

#[CoversClass(AssignmentsServiceInterface::class)]
class AssignmentsServiceTest extends TestCase
{
    private const int TEST_USER_ID = 1;
    private const string TEST_ROLE_NAME = 'admin';
    private const string TEST_PERMISSION_NAME = 'create_post';
    private const string TEST_USER_LOGIN = 'test@example.com';

    private AssignmentsStorageInterface $assignmentsStorage;
    private AccessRightsServiceInterface $accessRightsService;
    private UserQueryServiceInterface $userQueryService;
    private Manager $manager;
    private AssignmentsService $assignmentsService;
    private ItemsStorage $storage;
    private AssignAccessService $assignService;

    public function testGetUserIdsByRole(): void
    {
        $roleDTO = new RoleDTO(self::TEST_ROLE_NAME);
        $expectedUserIds = [(string)self::TEST_USER_ID, '2', '3'];

        $this->manager->addRole(new Role(self::TEST_ROLE_NAME));
        $this->manager->assign(self::TEST_ROLE_NAME, self::TEST_USER_ID);
        $this->manager->assign(self::TEST_ROLE_NAME, 2);
        $this->manager->assign(self::TEST_ROLE_NAME, 3);


        $result = $this->assignmentsService->getUserIdsByRole($roleDTO);

        $this->assertSame($expectedUserIds, $result);
    }

    public function testGetRolesByUser(): void
    {
        $role = new Role(self::TEST_ROLE_NAME);
        $roleDTO = new RoleDTO(self::TEST_ROLE_NAME);

        $this->manager->addRole($role);
        $this->manager->assign(self::TEST_ROLE_NAME, self::TEST_USER_ID);

        $result = $this->assignmentsService->getRolesByUser(self::TEST_USER_ID);

        $this->assertArrayHasKey(self::TEST_ROLE_NAME, $result);
        $this->assertSame($roleDTO->getName(), $result[self::TEST_ROLE_NAME]->getName());
    }

    public function testGetPermissionsByUser(): void
    {
        $permission = new Permission(self::TEST_PERMISSION_NAME);

        $this->manager->addPermission($permission);
        $this->manager->assign(self::TEST_PERMISSION_NAME, self::TEST_USER_ID);

        $result = $this->assignmentsService->getPermissionsByUser(self::TEST_USER_ID);

        $this->assertArrayHasKey(self::TEST_PERMISSION_NAME, $result);
        $this->assertInstanceOf(PermissionDTO::class, $result[self::TEST_PERMISSION_NAME]);
        $this->assertSame(self::TEST_PERMISSION_NAME, $result[self::TEST_PERMISSION_NAME]->getName());
    }

    public function testUserHasPermission(): void
    {
        $this->manager->addPermission(new Permission(self::TEST_PERMISSION_NAME));
        $this->manager->assign(self::TEST_PERMISSION_NAME, self::TEST_USER_ID);

        $result = $this->assignmentsService->userHasPermission(self::TEST_USER_ID, self::TEST_PERMISSION_NAME);

        $this->assertTrue($result);
    }

    public function testUserHasRole(): void
    {
        $this->manager->addRole(new Role(self::TEST_ROLE_NAME));
        $this->manager->assign(self::TEST_ROLE_NAME, self::TEST_USER_ID);

        $result = $this->assignmentsService->userHasRole(self::TEST_USER_ID, self::TEST_ROLE_NAME);

        $this->assertTrue($result);
    }

    public function testUserDoesNotHaveRole(): void
    {
        $result = $this->assignmentsService->userHasRole(self::TEST_USER_ID, self::TEST_ROLE_NAME);

        $this->assertFalse($result);
    }

    public function testIsAssignedRoleToUsers(): void
    {
        $this->manager->addRole(new Role(self::TEST_ROLE_NAME));
        $this->manager->assign(self::TEST_ROLE_NAME, self::TEST_USER_ID);

        $roleDTO = new RoleDTO(self::TEST_ROLE_NAME);

        $result = $this->assignmentsService->isAssignedRoleToUsers($roleDTO);

        $this->assertTrue($result);
    }

    public function testIsAssignedPermissionToUsers(): void
    {
        $permissionDTO = new PermissionDTO(self::TEST_PERMISSION_NAME);

        $this->manager->addPermission(new Permission(self::TEST_PERMISSION_NAME));
        $this->manager->assign(self::TEST_PERMISSION_NAME, self::TEST_USER_ID);

        $result = $this->assignmentsService->isAssignedPermissionToUsers($permissionDTO);

        $this->assertTrue($result);
    }

    public function testGetUserAssignments(): void
    {
        $user = $this->createUser();

        $this->manager->addRole(new Role(self::TEST_ROLE_NAME));
        $this->manager->addPermission(new Permission(self::TEST_PERMISSION_NAME));
        $this->manager->assign(self::TEST_ROLE_NAME, self::TEST_USER_ID);
        $this->manager->assign(self::TEST_PERMISSION_NAME, self::TEST_USER_ID);

        $result = $this->assignmentsService->getUserAssignments($user);

        $this->assertInstanceOf(UserAssignmentsDTO::class, $result);
        $this->assertArrayHasKey(self::TEST_ROLE_NAME, $result->getRoles());
        $this->assertArrayHasKey(self::TEST_PERMISSION_NAME, $result->getPermissions());
    }

    public function testGetAssignments(): void
    {
        $user = $this->createUser();

        $this->manager->addRole(new Role(self::TEST_ROLE_NAME));
        $this->manager->addPermission(new Permission(self::TEST_PERMISSION_NAME));
        $this->manager->assign(self::TEST_ROLE_NAME, self::TEST_USER_ID);
        $this->manager->assign(self::TEST_PERMISSION_NAME, self::TEST_USER_ID);

        $this->userQueryService
            ->expects($this->once())
            ->method('getUsers')
            ->willReturn([$user]);

        $result = $this->assignmentsService->getAssignments();

        $this->assertArrayHasKey(self::TEST_USER_ID, $result);
        $this->assertInstanceOf(UserAssignmentsDTO::class, $result[self::TEST_USER_ID]);
        $this->assertCount(1, $result[self::TEST_USER_ID]->getRoles());
        $this->assertCount(1, $result[self::TEST_USER_ID]->getPermissions());
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
        $this->userQueryService = $this->createMock(UserQueryServiceInterface::class);
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
            $this->userQueryService,
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

    /**
     * @throws Exception
     */
    private function createUser(): User
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(self::TEST_USER_ID);
        $user->method('getLogin')->willReturn(self::TEST_USER_LOGIN);
        return $user;
    }
}
