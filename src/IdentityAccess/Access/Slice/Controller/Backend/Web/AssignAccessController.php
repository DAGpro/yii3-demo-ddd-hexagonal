<?php

declare(strict_types=1);

namespace App\IdentityAccess\Access\Slice\Controller\Backend\Web;

use App\IdentityAccess\Access\Domain\Exception\AssignedItemException;
use App\IdentityAccess\Access\Domain\Exception\NotExistItemException;
use App\IdentityAccess\Access\Slice\Service\AssignAccessServiceInterface;
use App\IdentityAccess\Access\Slice\Service\PermissionDTO;
use App\IdentityAccess\Access\Slice\Service\RoleDTO;
use App\Infrastructure\Presentation\Web\Service\WebControllerService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Throwable;

final readonly class AssignAccessController
{
    public function __construct(
        private AssignAccessServiceInterface $assignAccessService,
        private WebControllerService $webService,
    ) {}

    public function assignRole(Request $request): ResponseInterface
    {
        $body = $request->getParsedBody();
        $userId = !empty($body['user_id']) ? (int) $body['user_id'] : null;
        $roleName = !empty($body['role']) ? (string) $body['role'] : null;

        if ($userId === null || $roleName === null) {
            return $this->webService->sessionFlashAndRedirect(
                'The request must be a POST method and receive two parameters user_id and role!',
                'backend/access/assignments',
                [],
                'danger',
            );
        }

        try {
            $this->assignAccessService->assignRole(new RoleDTO($roleName), $userId);

            return $this->webService->sessionFlashAndRedirect(
                'Role successfully assigned',
                'backend/access/user-assignments',
                ['user_id' => $userId],
            );
        } catch (NotExistItemException|AssignedItemException $t) {
            return $this->webService->sessionFlashAndRedirect(
                $t->getMessage(),
                'backend/access/user-assignments',
                ['user_id' => $userId],
                'danger',
            );
        }
    }

    public function revokeRole(Request $request): ResponseInterface
    {
        $body = $request->getParsedBody();
        $userId = !empty($body['user_id']) ? (int) $body['user_id'] : null;
        $roleName = !empty($body['role']) ? (string) $body['role'] : null;

        if ($userId === null || $roleName === null) {
            return $this->webService->sessionFlashAndRedirect(
                'The request must be a POST method and receive two parameters user_id and role!',
                'backend/access/assignments',
                [],
                'danger',
            );
        }

        try {
            $this->assignAccessService->revokeRole(new RoleDTO($roleName), $userId);

            return $this->webService->sessionFlashAndRedirect(
                'Role successfully revoked!',
                'backend/access/user-assignments',
                ['user_id' => $userId],
            );
        } catch (AssignedItemException $t) {
            return $this->webService->sessionFlashAndRedirect(
                $t->getMessage(),
                'backend/access/user-assignments',
                ['user_id' => $userId],
                'danger',
            );
        }
    }

    public function assignPermission(Request $request): ResponseInterface
    {
        $body = $request->getParsedBody();
        $userId = !empty($body['user_id']) ? (int) $body['user_id'] : null;
        $permissionName = !empty($body['permission']) ? (string) $body['permission'] : null;

        if ($userId === null || $permissionName === null) {
            return $this->webService->sessionFlashAndRedirect(
                'The request must be a POST method and receive two parameters user_id and permission!',
                'backend/access/assignments',
                [],
                'danger',
            );
        }

        try {
            $this->assignAccessService->assignPermission(new PermissionDTO($permissionName), $userId);

            return $this->webService->sessionFlashAndRedirect(
                'Permission successfully assigned!',
                'backend/access/user-assignments',
                ['user_id' => $userId],
            );
        } catch (NotExistItemException|AssignedItemException $t) {
            return $this->webService->sessionFlashAndRedirect(
                $t->getMessage(),
                'backend/access/user-assignments',
                ['user_id' => $userId],
                'danger',
            );
        }
    }

    public function revokePermission(Request $request): ResponseInterface
    {
        $body = $request->getParsedBody();
        $userId = !empty($body['user_id']) ? (int) $body['user_id'] : null;
        $permissionName = !empty($body['permission']) ? (string) $body['permission'] : null;

        if ($userId === null || $permissionName === null) {
            return $this->webService->sessionFlashAndRedirect(
                'The request must be a POST method and receive two parameters user_id and permission!',
                'backend/access/assignments',
                [],
                'danger',
            );
        }

        try {
            $this->assignAccessService->revokePermission(new PermissionDTO($permissionName), $userId);

            return $this->webService->sessionFlashAndRedirect(
                'Permission successfully revoked!',
                'backend/access/user-assignments',
                ['user_id' => $userId],
            );
        } catch (AssignedItemException $t) {
            return $this->webService->sessionFlashAndRedirect(
                $t->getMessage(),
                'backend/access/user-assignments',
                ['user_id' => $userId],
                'danger',
            );
        }
    }

    public function revokeAll(Request $request): ResponseInterface
    {
        $body = $request->getParsedBody();
        $userId = !empty($body['user_id']) ? (int) $body['user_id'] : null;

        if ($userId === null) {
            return $this->webService->sessionFlashAndRedirect(
                'The request must be a POST method and have a POST parameter user_id!',
                'backend/access/assignments',
                [],
                'danger',
            );
        }

        try {
            $this->assignAccessService->revokeAll($userId);

            return $this->webService->sessionFlashAndRedirect(
                'User assignments successfully revoked',
                'backend/access/user-assignments',
                ['user_id' => $userId],
            );
        } catch (Throwable $t) {
            return $this->webService->sessionFlashAndRedirect(
                $t->getMessage(),
                'backend/access/user-assignments',
                ['user_id' => $userId],
                'danger',
            );
        }
    }

    public function clearAssignments(): ResponseInterface
    {
        try {
            $this->assignAccessService->clearAssignments();

            return $this->webService->sessionFlashAndRedirect(
                'Assignments all users successfully revoked',
                'backend/access/assignments',
            );
        } catch (Throwable $t) {
            return $this->webService->sessionFlashAndRedirect(
                $t->getMessage(),
                'backend/access/assignments',
                [],
                'danger',
            );
        }
    }
}
