<?php

declare(strict_types=1);

namespace App\IdentityAccess\Presentation\Backend\Web\Access;

use App\IdentityAccess\Access\Application\Service\AccessManagementServiceInterface;
use App\IdentityAccess\Access\Application\Service\PermissionDTO;
use App\IdentityAccess\Access\Application\Service\RoleDTO;
use App\IdentityAccess\Access\Domain\Exception\AssignedItemException;
use App\IdentityAccess\Access\Domain\Exception\ExistItemException;
use App\IdentityAccess\Access\Domain\Exception\NotExistItemException;
use App\Infrastructure\Presentation\Web\Service\WebControllerService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;

final readonly class AccessManagementController
{
    public function __construct(private AccessManagementServiceInterface $accessManagementService, private WebControllerService $webService)
    {
    }

    public function addRole(Request $request): ResponseInterface
    {
        $body = $request->getParsedBody();
        $roleName = !empty($body['role']) ? $body['role'] : null;
        if ($roleName === null) {
            return $this->webService->sessionFlashAndRedirect(
                'No required role parameter in POST request',
                'backend/access',
                [],
                'danger'
            );
        }

        try {
            $roleDTO = new RoleDTO($roleName);
            $this->accessManagementService->addRole($roleDTO);

            return $this->webService->sessionFlashAndRedirect(
                'Role successfully added!',
                'backend/access'
            );
        } catch (ExistItemException $t) {
            return $this->webService->sessionFlashAndRedirect(
                $t->getMessage(),
                'backend/access',
                [],
                'danger'
            );
        }
    }

    public function removeRole(Request $request): ResponseInterface
    {
        $body = $request->getParsedBody();
        $roleName = !empty($body['role']) ? $body['role'] : null;
        if ($roleName === null) {
            return $this->webService->sessionFlashAndRedirect(
                'No required role parameter in POST request',
                'backend/access',
                [],
                'danger'
            );
        }

        try {
            $roleDTO = new RoleDTO($roleName);
            $this->accessManagementService->removeRole($roleDTO);

            return $this->webService->sessionFlashAndRedirect(
                'Role successfully removed!',
                'backend/access'
            );
        } catch (NotExistItemException|AssignedItemException $t) {
            return $this->webService->sessionFlashAndRedirect(
                $t->getMessage(),
                'backend/access',
                [],
                'danger'
        );
        }
    }

    public function addPermission(Request $request): ResponseInterface
    {
        $body = $request->getParsedBody();
        $permissionName = !empty($body['permission']) ? $body['permission'] : null;
        if ($permissionName === null) {
            return $this->webService->sessionFlashAndRedirect(
                'No required permission parameter in POST request',
                'backend/access/permissions',
                [],
                'danger'
            );
        }

        try {
            $permissionDTO = new PermissionDTO($permissionName);
            $this->accessManagementService->addPermission($permissionDTO);

            return $this->webService->sessionFlashAndRedirect(
                'Permission successfully added!',
                'backend/access/permissions'
            );
        } catch (ExistItemException $t) {
            return $this->webService->sessionFlashAndRedirect(
                $t->getMessage(),
                'backend/access/permissions',
                [],
                'danger'
            );
        }
    }

    public function removePermission(Request $request): ResponseInterface
    {
        $body = $request->getParsedBody();
        $permissionName = !empty($body['permission']) ? $body['permission'] : null;
        if ($permissionName === null) {
            return $this->webService->sessionFlashAndRedirect(
                'No required permission parameter in POST request',
                'backend/access/permissions',
                [],
                'danger'
            );
        }

        try {
            $permissionDTO = new PermissionDTO($permissionName);
            $this->accessManagementService->removePermission($permissionDTO);

            return $this->webService->sessionFlashAndRedirect(
                'Permission successfully removed!',
                'backend/access/permissions'
            );
        } catch (NotExistItemException|AssignedItemException $t) {
            return $this->webService->sessionFlashAndRedirect(
                $t->getMessage(),
                'backend/access/permissions',
                [],
                'danger'
            );
        }
    }

    public function addChildRole(Request $request): ResponseInterface
    {
        $body = $request->getParsedBody();
        $parentRoleName = !empty($body['parent_role']) ? $body['parent_role'] : null;
        $childRoleName = !empty($body['child_role']) ? $body['child_role'] : null;
        if ($parentRoleName === null || $childRoleName === null) {
            return $this->webService->sessionFlashAndRedirect(
                'No required parent role and child role parameters in POST request',
                'backend/access',
                [],
                'danger'
            );
        }

        try {
            $parentRoleDTO = new RoleDTO($parentRoleName);
            $childRoleDTO = new RoleDTO($childRoleName);
            $this->accessManagementService->addChildRole($parentRoleDTO, $childRoleDTO);

            return $this->webService->sessionFlashAndRedirect(
                'Child role successfully added!',
                'backend/access/view-role',
                ['role_name' => $parentRoleName]
            );
        } catch (ExistItemException|NotExistItemException $t) {
            return $this->webService->sessionFlashAndRedirect(
                $t->getMessage(),
                'backend/access/view-role',
                ['role_name' => $parentRoleName],
                'danger'
            );
        }
    }

    public function removeChildRole(Request $request): ResponseInterface
    {
        $body = $request->getParsedBody();
        $parentRoleName = !empty($body['parent_role']) ? $body['parent_role'] : null;
        $childRoleName = !empty($body['child_role']) ? $body['child_role'] : null;
        if ($parentRoleName === null || $childRoleName === null) {
            return $this->webService->sessionFlashAndRedirect(
                'No required parent role and child role parameters in POST request',
                'backend/access',
                [],
                'danger'
            );
        }

        try {
            $parentRoleDTO = new RoleDTO($parentRoleName);
            $childRoleDTO = new RoleDTO($childRoleName);
            $this->accessManagementService->removeChildRole($parentRoleDTO, $childRoleDTO);

            return $this->webService->sessionFlashAndRedirect(
                'Child Role successfully removed!',
                'backend/access/view-role',
                ['role_name' => $parentRoleName]
            );
        } catch (NotExistItemException $t) {
            return $this->webService->sessionFlashAndRedirect(
                $t->getMessage(),
                'backend/access/view-role',
                ['role_name' => $parentRoleName],
                'danger'
            );
        }
    }

    public function addChildPermission(Request $request): ResponseInterface
    {
        $body = $request->getParsedBody();
        $parentRoleName = !empty($body['parent_role']) ? $body['parent_role'] : null;
        $childPermissionName = !empty($body['child_permission']) ? $body['child_permission'] : null;
        if ($parentRoleName === null || $childPermissionName === null) {
            return $this->webService->sessionFlashAndRedirect(
                'No required parent role and child permission parameters in POST request',
                'backend/access',
                [],
                'danger'
            );
        }

        try {
            $parentRoleDTO = new RoleDTO($parentRoleName);
            $childPermissionDTO = new PermissionDTO($childPermissionName);
            $this->accessManagementService->addChildPermission($parentRoleDTO, $childPermissionDTO);

            return $this->webService->sessionFlashAndRedirect(
                'Child permission successfully added!',
                'backend/access/view-role',
                ['role_name' => $parentRoleName]
            );
        } catch (ExistItemException|NotExistItemException $t) {
            return $this->webService->sessionFlashAndRedirect(
                $t->getMessage(),
                'backend/access/view-role',
                ['role_name' => $parentRoleName],
                'danger'
            );
        }
    }

    public function removeChildPermission(Request $request): ResponseInterface
    {
        $body = $request->getParsedBody();
        $parentRoleName = !empty($body['parent_role']) ? $body['parent_role'] : null;
        $childPermissionName = !empty($body['child_permission']) ? $body['child_permission'] : null;
        if ($parentRoleName === null || $childPermissionName === null) {
            return $this->webService->sessionFlashAndRedirect(
                'No required parent role and child permission parameters in POST request',
                'backend/access',
                [],
                'danger'
            );
        }

        try {
            $parentRoleDTO = new RoleDTO($parentRoleName);
            $childPermissionDTO = new PermissionDTO($childPermissionName);
            $this->accessManagementService->removeChildPermission($parentRoleDTO, $childPermissionDTO);

            return $this->webService->sessionFlashAndRedirect(
                'Child permission successfully removed!',
                'backend/access/view-role',
                ['role_name' => $parentRoleName]
            );
        } catch (NotExistItemException $t) {
            return $this->webService->sessionFlashAndRedirect(
                $t->getMessage(),
                'backend/access',
                [],
                'danger'
            );
        }
    }

    public function removeChildren(Request $request): ResponseInterface
    {
        $body = $request->getParsedBody();
        $parentRoleName = !empty($body['parent_role']) ? $body['parent_role'] : null;
        if ($parentRoleName === null) {
            return $this->webService->sessionFlashAndRedirect(
                'No required parent role parameter in POST request',
                'backend/access',
                [],
                'danger'
            );
        }

        try {
            $parentRoleDTO = new RoleDTO($parentRoleName);
            $this->accessManagementService->removeChildren($parentRoleDTO);

            return $this->webService->sessionFlashAndRedirect(
                'Children successfully removed!',
                'backend/access/view-role',
                ['role_name' => $parentRoleName]
            );
        } catch (NotExistItemException $t) {
            return $this->webService->sessionFlashAndRedirect(
                $t->getMessage(),
                'backend/access/view-role',
                ['role_name' => $parentRoleName],
                'danger',
            );
        }
    }

    public function clearAccessRights(): ResponseInterface
    {
        $this->accessManagementService->clearAccessRights();

        return $this->webService->sessionFlashAndRedirect(
            'Clear access rights!',
            'backend/access'
        );
    }
}
