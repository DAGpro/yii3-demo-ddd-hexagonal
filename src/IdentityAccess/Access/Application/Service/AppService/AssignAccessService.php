<?php

declare(strict_types=1);

namespace App\IdentityAccess\Access\Application\Service\AppService;

use App\IdentityAccess\Access\Application\Service\AccessRightsServiceInterface;
use App\IdentityAccess\Access\Application\Service\AssignAccessServiceInterface;
use App\IdentityAccess\Access\Application\Service\AssignmentsServiceInterface;
use App\IdentityAccess\Access\Application\Service\PermissionDTO;
use App\IdentityAccess\Access\Application\Service\RoleDTO;
use App\IdentityAccess\Access\Domain\Exception\AssignedItemException;
use App\IdentityAccess\Access\Domain\Exception\NotExistItemException;
use Yiisoft\Rbac\AssignmentsStorageInterface;
use Yiisoft\Rbac\Manager;
use Yiisoft\Rbac\Permission;
use Yiisoft\Rbac\Role;

final readonly class AssignAccessService implements AssignAccessServiceInterface
{
    public function __construct(
        private Manager $manager,
        private AccessRightsServiceInterface $accessRightsService,
        private AssignmentsStorageInterface $storage,
        private AssignmentsServiceInterface $assignmentsService,
    ) {}

    /**
     * @throws AssignedItemException
     * @throws NotExistItemException
     */
    #[\Override]
    public function assignRole(RoleDTO $roleDTO, string|int $userId): void
    {
        if (!$this->accessRightsService->existRole($roleDTO->getName())) {
            throw new NotExistItemException('This role does not exist!');
        }

        if ($this->assignmentsService->userHasRole($userId, $roleDTO->getName())) {
            throw new AssignedItemException('The role has already been assigned to the user!');
        }

        $role = new Role($roleDTO->getName());
        $this->manager->assign($role->getName(), $userId);
    }

    /**
     * @throws AssignedItemException
     * @throws NotExistItemException
     */
    #[\Override]
    public function assignPermission(PermissionDTO $permissionDTO, string|int $userId): void
    {
        if (!$this->accessRightsService->existPermission($permissionDTO->getName())) {
            throw new NotExistItemException('This permission does not exist!');
        }

        if ($this->assignmentsService->userHasPermission($userId, $permissionDTO->getName())) {
            throw new AssignedItemException('The permission has already been assigned to the user!');
        }

        $permission = new Permission($permissionDTO->getName());
        $this->manager->assign($permission->getName(), $userId);
    }

    /**
     * @throws AssignedItemException
     */
    #[\Override]
    public function revokeRole(RoleDTO $roleDTO, string|int $userId): void
    {
        if (!$this->assignmentsService->userHasRole($userId, $roleDTO->getName())) {
            throw new AssignedItemException('The role was not previously assigned to the user!');
        }

        $role = new Role($roleDTO->getName());
        $this->manager->revoke($role->getName(), $userId);
    }

    /**
     * @throws AssignedItemException
     */
    #[\Override]
    public function revokePermission(PermissionDTO $permissionDTO, string|int $userId): void
    {
        if (!$this->assignmentsService->userHasPermission($userId, $permissionDTO->getName())) {
            throw new AssignedItemException('The permission was not previously assigned to the user!');
        }

        $permission = new Permission($permissionDTO->getName());
        $this->manager->revoke($permission->getName(), $userId);
    }

    #[\Override]
    public function revokeAll(string|int $userId): void
    {
        $this->manager->revokeAll($userId);
    }

    #[\Override]
    public function clearAssignments(): void
    {
        $this->storage->clear();
    }
}
