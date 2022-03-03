<?php

declare(strict_types=1);

namespace App\Presentation\Backend\Web\Component\IdentityAccess\Access;

use App\Core\Component\IdentityAccess\Access\Application\Service\AssignAccessServiceInterface;
use App\Core\Component\IdentityAccess\Access\Application\Service\PermissionDTO;
use App\Core\Component\IdentityAccess\Access\Application\Service\RoleDTO;
use App\Core\Component\IdentityAccess\Access\Domain\Exception\AssignedItemException;
use App\Core\Component\IdentityAccess\Access\Domain\Exception\NotExistItemException;
use App\Presentation\Infrastructure\Web\Service\WebControllerService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;

class AssignAccessController
{
    private WebControllerService $webService;
    private AssignAccessServiceInterface $assignAccessService;

    public function __construct(
        AssignAccessServiceInterface $assignAccessService,
        WebControllerService $webService,
    ) {
        $this->webService = $webService;
        $this->assignAccessService = $assignAccessService;
    }

    public function assignRole(Request $request): ResponseInterface
    {
        $body = $request->getParsedBody();
        $userId = !empty($body['user_id']) ? $body['user_id'] : null;
        $roleName = !empty($body['role']) ? $body['role'] : null;

        if ($userId === null || $roleName === null) {
            return $this->webService->sessionFlashAndRedirect(
                'The request must be a POST method and receive two parameters user_id and role!',
                'backend/access/assignments',
                [],
                'danger'
            );
        }

        try {
            $this->assignAccessService->assignRole(new RoleDTO($roleName), $userId);

            return $this->webService->sessionFlashAndRedirect(
                'Role successfully assigned',
                'backend/access/user-assignments',
                ['user_id' => $userId]
            );
        } catch (NotExistItemException|AssignedItemException $t) {
            return $this->webService->sessionFlashAndRedirect(
                $t->getMessage(),
                'backend/access/user-assignments',
                ['user_id' => $userId],
                'danger'
            );
        }
    }

    public function revokeRole(Request $request): ResponseInterface
    {
        $body = $request->getParsedBody();
        $userId = !empty($body['user_id']) ? $body['user_id'] : null;
        $roleName = !empty($body['role']) ? $body['role'] : null;

        if ($userId === null  || $roleName === null) {
            return $this->webService->sessionFlashAndRedirect(
                'The request must be a POST method and receive two parameters user_id and role!',
                'backend/access/assignments',
                [],
                'danger'
            );
        }

        try {
            $this->assignAccessService->revokeRole(new RoleDTO($roleName), $userId);

            return $this->webService->sessionFlashAndRedirect(
                'Role successfully revoked!',
                'backend/access/user-assignments',
                ['user_id' => $userId]
            );
        } catch (AssignedItemException $t) {
            return $this->webService->sessionFlashAndRedirect(
                $t->getMessage(),
                'backend/access/user-assignments',
                ['user_id' => $userId],
                'danger'
            );
        }
    }

    public function assignPermission(Request $request): ResponseInterface
    {
        $body = $request->getParsedBody();
        $userId = !empty($body['user_id']) ? $body['user_id'] : null;
        $permissionName = !empty($body['permission']) ? $body['permission'] : null;

        if ($userId === null  || $permissionName === null) {
            return $this->webService->sessionFlashAndRedirect(
                'The request must be a POST method and receive two parameters user_id and permission!',
                'backend/access/assignments',
                [],
                'danger'
            );
        }

        try {
            $this->assignAccessService->assignPermission(new PermissionDTO($permissionName), $userId);

            return $this->webService->sessionFlashAndRedirect(
                'Permission successfully assigned!',
                'backend/access/user-assignments',
                ['user_id' => $userId]
            );
        } catch (NotExistItemException|AssignedItemException $t) {
            return $this->webService->sessionFlashAndRedirect(
                $t->getMessage(),
                'backend/access/user-assignments',
                ['user_id' => $userId],
                'danger'
            );
        }
    }

    public function revokePermission(Request $request): ResponseInterface
    {
        $body = $request->getParsedBody();
        $userId = !empty($body['user_id']) ? $body['user_id'] : null;
        $permissionName = !empty($body['permission']) ? $body['permission'] : null;

        if ($userId === null  || $permissionName === null) {
            return $this->webService->sessionFlashAndRedirect(
                'The request must be a POST method and receive two parameters user_id and permission!',
                'backend/access/assignments',
                [],
                'danger'
            );
       }

        try {
            $this->assignAccessService->revokePermission(new PermissionDTO($permissionName), $userId);

            return $this->webService->sessionFlashAndRedirect(
                'Permission successfully revoked!',
                'backend/access/user-assignments',
                ['user_id' => $userId]
            );
        } catch (AssignedItemException $t) {
            return $this->webService->sessionFlashAndRedirect(
                $t->getMessage(),
                'backend/access/user-assignments',
                ['user_id' => $userId],
                'danger'
            );
        }
    }

    public function revokeAll(Request $request): ResponseInterface
    {
        $body = $request->getParsedBody();
        $userId = !empty($body['user_id']) ? $body['user_id'] : null;

        if ($userId === null) {
            return $this->webService->sessionFlashAndRedirect(
                'The request must be a POST method and have a POST parameter user_id!',
                'backend/access/assignments',
                [],
                'danger'
            );
        }

        try {
            $this->assignAccessService->revokeAll($userId);

            return $this->webService->sessionFlashAndRedirect(
                'User assignments successfully revoked',
                'backend/access/user-assignments',
                ['user_id' => $userId]
            );
        } catch (\Throwable $t) {
            return $this->webService->sessionFlashAndRedirect(
                $t->getMessage(),
                'backend/access/user-assignments',
                ['user_id' => $userId],
                'danger'
            );
        }
    }

    public function clearAssignments(): ResponseInterface
    {
        try {
            $this->assignAccessService->clearAssignments();

            return $this->webService->sessionFlashAndRedirect(
                'Assignments all users successfully revoked',
                'backend/access/assignments'
            );
        } catch (\Throwable $t) {
            return $this->webService->sessionFlashAndRedirect(
                $t->getMessage(),
                'backend/access/assignments',
                [],
                'danger'
            );
        }
    }
}
