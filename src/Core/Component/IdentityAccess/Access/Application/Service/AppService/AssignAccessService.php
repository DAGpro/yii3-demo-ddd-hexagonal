<?php

declare(strict_types=1);

namespace App\Core\Component\IdentityAccess\Access\Application\Service\AppService;

use App\Core\Component\IdentityAccess\Access\Application\Service\AssignAccessServiceInterface;
use App\Core\Component\IdentityAccess\Access\Application\Service\AssignmentsServiceInterface;
use App\Core\Component\IdentityAccess\Access\Application\Service\PermissionDTO;
use App\Core\Component\IdentityAccess\Access\Application\Service\RoleDTO;
use App\Core\Component\IdentityAccess\Access\Domain\Exception\AssignedItemException;
use Yiisoft\Rbac\AssignmentsStorageInterface;
use Yiisoft\Rbac\Manager;
use Yiisoft\Rbac\Permission;
use Yiisoft\Rbac\Role;

class AssignAccessService implements AssignAccessServiceInterface
{
    private Manager $manager;
    private AssignmentsStorageInterface $storage;
    private AssignmentsServiceInterface $assignmentsService;

    public function __construct(
        Manager $manager,
        AssignmentsStorageInterface $storage,
        AssignmentsServiceInterface $assignmentsService
    ) {
        $this->manager = $manager;
        $this->storage = $storage;
        $this->assignmentsService = $assignmentsService;
    }

    /**
     * @throws AssignedItemException
     */
    public function assignRole(RoleDTO $roleDTO, string $userId): void
    {
        if ($this->assignmentsService->userHasRole($userId, $roleDTO->getName())) {
            throw new AssignedItemException('The role has already been assigned to the user!');
        }

        $role = new Role($roleDTO->getName());
        $this->manager->assign($role, $userId);
    }

    /**
     * @throws AssignedItemException
     */
    public function assignPermission(PermissionDTO $permissionDTO, string $userId): void
    {
        if ($this->assignmentsService->userHasPermission($userId, $permissionDTO->getName())) {
            throw new AssignedItemException('The permission has already been assigned to the user!');
        }

        $permission = new Permission($permissionDTO->getName());
        $this->manager->assign($permission, $userId);
    }

    /**
     * @throws AssignedItemException
     */
    public function revokeRole(RoleDTO $roleDTO, string $userId): void
    {
        if (!$this->assignmentsService->userHasRole($userId, $roleDTO->getName())) {
            throw new AssignedItemException('The role was not previously assigned to the user!');
        }

        $role = new Role($roleDTO->getName());
        $this->manager->revoke($role, $userId);
    }

    /**
     * @throws AssignedItemException
     */
    public function revokePermission(PermissionDTO $permissionDTO, string $userId): void
    {
        if (!$this->assignmentsService->userHasPermission($userId, $permissionDTO->getName())) {
            throw new AssignedItemException('The permission was not previously assigned to the user!');
        }

        $permission = new Permission($permissionDTO->getName());
        $this->manager->revoke($permission, $userId);
    }

    public function revokeAll(string $userId): void
    {
        $this->manager->revokeAll($userId);
    }

    public function clearAssignments(): void
    {
        $this->storage->clearAssignments();
    }
}
