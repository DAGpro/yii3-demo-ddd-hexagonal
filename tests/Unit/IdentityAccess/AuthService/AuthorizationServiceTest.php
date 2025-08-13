<?php

declare(strict_types=1);

namespace App\Tests\Unit\IdentityAccess\AuthService;

use App\IdentityAccess\Access\Application\Service\AssignmentsServiceInterface;
use App\IdentityAccess\Access\Application\Service\RoleDTO;
use App\IdentityAccess\AuthService\AuthorizationService;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(AuthorizationService::class)]
class AuthorizationServiceTest extends TestCase
{
    private AuthorizationService $authorizationService;
    private MockObject|AssignmentsServiceInterface $assignmentsService;

    public function testUserHasPermissionWhenGranted(): void
    {
        $userId = 1;
        $permission = 'create_post';

        $this->assignmentsService
            ->expects($this->once())
            ->method('userHasPermission')
            ->with($userId, $permission)
            ->willReturn(true);

        $this->assertTrue($this->authorizationService->userHasPermission($userId, $permission));
    }

    public function testUserHasPermissionWhenDenied(): void
    {
        $userId = 1;
        $permission = 'edit_post';

        $this->assignmentsService
            ->expects($this->once())
            ->method('userHasPermission')
            ->with($userId, $permission)
            ->willReturn(false);

        $this->assertFalse($this->authorizationService->userHasPermission($userId, $permission));
    }

    public function testUserHasRoleDirectly(): void
    {
        $userId = 1;
        $roleName = 'admin';

        $this->assignmentsService
            ->expects($this->once())
            ->method('userHasRole')
            ->with($userId, $roleName)
            ->willReturn(true);

        $this->assertTrue($this->authorizationService->userHasRole($userId, $roleName));
    }

    public function testUserHasRoleThroughChildRole(): void
    {
        $userId = 1;
        $parentRole = 'admin';
        $childRole = 'editor';

        $this->assignmentsService
            ->expects($this->once())
            ->method('userHasRole')
            ->with($userId, $parentRole)
            ->willReturn(false);

        $roleDto = new RoleDTO('editor', 'Editor role');
        $parentRoleDto = new RoleDTO($parentRole, 'Parent role');
        $roleDto->withChildRoles([$parentRole => $parentRoleDto]);

        $this->assignmentsService
            ->expects($this->once())
            ->method('getRolesByUser')
            ->with($userId)
            ->willReturn([$roleDto]);

        $this->assertTrue($this->authorizationService->userHasRole($userId, $parentRole));
    }

    public function testUserDoesNotHaveRole(): void
    {
        $userId = 1;
        $roleName = 'admin';

        $this->assignmentsService
            ->expects($this->once())
            ->method('userHasRole')
            ->with($userId, $roleName)
            ->willReturn(false);

        $this->assignmentsService
            ->expects($this->once())
            ->method('getRolesByUser')
            ->with($userId)
            ->willReturn([]);

        $this->assertFalse($this->authorizationService->userHasRole($userId, $roleName));
    }

    public function testUserHasRoleThroughNestedRoles(): void
    {
        $userId = 1;
        $grandParentRole = 'superadmin';
        $parentRole = 'admin';
        $childRole = 'editor';
        
        $this->assignmentsService
            ->expects($this->once())
            ->method('userHasRole')
            ->with($userId, $grandParentRole)
            ->willReturn(false);

        $roleDto = new RoleDTO('editor', 'Editor role');
        $parentRoleDto = new RoleDTO($parentRole, 'Parent role');
        $grandParentRoleDto = new RoleDTO($grandParentRole, 'Grandparent role');
        $roleDto->withChildRoles([$parentRole => $parentRoleDto]);
        $roleDto->withNestedRoles([$grandParentRole => $grandParentRoleDto]);

        $this->assignmentsService
            ->expects($this->once())
            ->method('getRolesByUser')
            ->with($userId)
            ->willReturn([$roleDto]);

        $this->assertTrue($this->authorizationService->userHasRole($userId, $grandParentRole));
    }

    /**
     * @throws Exception
     */
    #[Override]
    protected function setUp(): void
    {
        $this->assignmentsService = $this->createMock(AssignmentsServiceInterface::class);
        $this->authorizationService = new AuthorizationService($this->assignmentsService);
    }
}
