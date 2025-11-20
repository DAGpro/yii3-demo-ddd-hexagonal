<?php

declare(strict_types=1);

namespace App\Tests\Unit\IdentityAccess\Access\Application\Service\AppService;

use App\IdentityAccess\Access\Application\Service\AccessRightsServiceInterface;
use App\IdentityAccess\Access\Application\Service\AppService\AccessRightsService;
use App\IdentityAccess\Access\Application\Service\AppService\AssignAccessService;
use App\IdentityAccess\Access\Application\Service\AppService\AssignmentsService;
use App\IdentityAccess\Access\Application\Service\PermissionDTO;
use App\IdentityAccess\Access\Application\Service\RoleDTO;
use App\IdentityAccess\Access\Application\Service\UserAssignmentsDTO;
use App\IdentityAccess\User\Application\Service\UserQueryServiceInterface;
use App\IdentityAccess\User\Domain\User;
use App\Tests\UnitTester;
use Codeception\Test\Unit;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;
use Yiisoft\Rbac\AssignmentsStorageInterface;
use Yiisoft\Rbac\ItemsStorageInterface;
use Yiisoft\Rbac\Manager;
use Yiisoft\Rbac\Permission;
use Yiisoft\Rbac\Php\AssignmentsStorage;
use Yiisoft\Rbac\Php\ItemsStorage;
use Yiisoft\Rbac\Role;
use Yiisoft\Rbac\RuleFactoryInterface;

#[CoversClass(AssignmentsService::class)]
class AssignmentsServiceTest extends Unit
{
    private const int TEST_USER_ID = 1;

    private const string TEST_ROLE_NAME = 'admin';

    private const string TEST_PERMISSION_NAME = 'create_post';

    private const string TEST_USER_LOGIN = 'test@example.com';

    private static ?AssignmentsStorageInterface $assignmentsStorage = null;

    private static ?ItemsStorageInterface $storage = null;

    private static ?string $tempDir = null;

    protected UnitTester $tester;

    private AccessRightsServiceInterface $accessRightsService;

    private UserQueryServiceInterface&MockObject $userQueryService;

    private Manager $manager;

    private AssignmentsService $assignmentsService;

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

    public function testGetRolesByUserReturnsEmptyArrayForUserWithNoRoles(): void
    {
        $result = $this->assignmentsService->getRolesByUser(self::TEST_USER_ID);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testGetRolesByUserReturnsAssignedRoles(): void
    {
        $role1 = new Role(self::TEST_ROLE_NAME);
        $role2 = new Role('moderator');

        $this->manager->addRole($role1);
        $this->manager->addRole($role2);
        $this->manager->assign(self::TEST_ROLE_NAME, self::TEST_USER_ID);
        $this->manager->assign('moderator', self::TEST_USER_ID);

        $result = $this->assignmentsService->getRolesByUser(self::TEST_USER_ID);

        $this->assertCount(2, $result);
        $this->assertArrayHasKey(self::TEST_ROLE_NAME, $result);
        $this->assertArrayHasKey('moderator', $result);
        $this->assertInstanceOf(RoleDTO::class, $result[self::TEST_ROLE_NAME]);
        $this->assertInstanceOf(RoleDTO::class, $result['moderator']);
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

    public function testGetUserAssignmentsForUserWithNoAssignments(): void
    {
        $user = $this->createUser();

        $result = $this->assignmentsService->getUserAssignments($user);

        $this->assertInstanceOf(UserAssignmentsDTO::class, $result);
        $this->assertEmpty($result->getRoles());
        $this->assertEmpty($result->getPermissions());
        $this->assertFalse($result->existRoles());
        $this->assertFalse($result->existPermissions());
    }

    public function testGetUserAssignmentsThrowException(): void
    {
        $user = $this->createUser(null);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('User id is null');

        $this->assignmentsService->getUserAssignments($user);
    }

    public function testGetUserAssignmentsWithRolesAndPermissions(): void
    {
        $user = $this->createUser();
        $role = new Role(self::TEST_ROLE_NAME)->withDescription('Administrator');
        $permission = new Permission(self::TEST_PERMISSION_NAME)->withDescription('Create Post');

        $this->manager->addRole($role);
        $this->manager->addPermission($permission);
        $this->manager->assign(self::TEST_ROLE_NAME, self::TEST_USER_ID);
        $this->manager->assign(self::TEST_PERMISSION_NAME, self::TEST_USER_ID);

        $result = $this->assignmentsService->getUserAssignments($user);

        $this->assertInstanceOf(UserAssignmentsDTO::class, $result);

        $roles = $result->getRoles();
        $this->assertCount(1, $roles);
        $this->assertArrayHasKey(self::TEST_ROLE_NAME, $roles);
        $this->assertInstanceOf(RoleDTO::class, $roles[self::TEST_ROLE_NAME]);
        $this->assertSame('Administrator', $roles[self::TEST_ROLE_NAME]->getDescription());

        $permissions = $result->getPermissions();
        $this->assertCount(1, $permissions);
        $this->assertArrayHasKey(self::TEST_PERMISSION_NAME, $permissions);
        $this->assertInstanceOf(PermissionDTO::class, $permissions[self::TEST_PERMISSION_NAME]);
        $this->assertSame('Create Post', $permissions[self::TEST_PERMISSION_NAME]->getDescription());

        $this->assertTrue($result->existRoles());
        $this->assertTrue($result->existPermissions());
    }

    public function testGetUserAssignmentsWithInheritedPermissions(): void
    {
        $adminRole = new Role(self::TEST_ROLE_NAME);
        $permission = new Permission(self::TEST_PERMISSION_NAME);

        $this->manager->addRole($adminRole);
        $this->manager->addPermission($permission);

        $this->manager->addChild(self::TEST_ROLE_NAME, self::TEST_PERMISSION_NAME);

        $this->manager->assign(self::TEST_ROLE_NAME, self::TEST_USER_ID);

        $user = $this->createUser();

        $result = $this->assignmentsService->getUserAssignments($user);

        $this->assertCount(1, $result->getRoles());
        $this->assertArrayHasKey(self::TEST_ROLE_NAME, $result->getRoles());
        $this->assertEmpty($result->getPermissions());
    }

    public function testGetAssignments(): void
    {
        $user1 = $this->createUser();
        $user2 = $this->createUser(null);

        $this->manager->addRole(new Role(self::TEST_ROLE_NAME));
        $this->manager->addPermission(new Permission(self::TEST_PERMISSION_NAME));
        $this->manager->assign(self::TEST_ROLE_NAME, self::TEST_USER_ID);
        $this->manager->assign(self::TEST_PERMISSION_NAME, self::TEST_USER_ID);

        $this->userQueryService
            ->expects($this->once())
            ->method('getUsers')
            ->willReturn([$user1, $user2]);

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
    protected function _before(): void
    {
        $this->userQueryService = $this->createMock(UserQueryServiceInterface::class);
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
            $this->userQueryService,
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

    /**
     * @throws Exception
     */
    private function createUser(?int $id = self::TEST_USER_ID): User&MockObject
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn($id);
        $user->method('getLogin')->willReturn(self::TEST_USER_LOGIN);
        return $user;
    }
}
