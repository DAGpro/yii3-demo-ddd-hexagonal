<?php

declare(strict_types=1);

namespace App\IdentityAccess\Presentation\Backend\Web\Access;

use App\IdentityAccess\Access\Application\Service\AccessRightsServiceInterface;
use App\Presentation\Infrastructure\Web\Service\WebControllerService;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Yii\View\ViewRenderer;

final class AccessRightsController
{
    private ViewRenderer $viewRenderer;
    private WebControllerService $webService;
    private AccessRightsServiceInterface $accessRightsService;

    public function __construct(
        ViewRenderer $viewRenderer,
        WebControllerService $webService,
        AccessRightsServiceInterface $accessRightsService
    ) {
        $this->webService = $webService;
        $this->accessRightsService = $accessRightsService;
        $viewRenderer = $viewRenderer->withLayout('@backendLayout/main');
        $viewRenderer = $viewRenderer->withViewPath('@identityBackendView/access');
        $this->viewRenderer = $viewRenderer->withControllerName('access-rights');
    }

    public function index(): ResponseInterface
    {
        $rolesWithChildren = $this->accessRightsService->getRoles();

        return $this->viewRenderer->render('index', [
            'roles' => $rolesWithChildren,
            'currentUrl' => 'roles',
        ]);
    }

    public function permissionsList(): ResponseInterface
    {
        $permissions = $this->accessRightsService->getPermissions();

        return $this->viewRenderer->render('permissions-list', [
            'permissions' => $permissions,
            'currentUrl' => 'permissions',
        ]);
    }

    public function viewRole(CurrentRoute $currentRoute): ResponseInterface
    {
        $roleName = $currentRoute->getArgument('role_name');
        if (null === $roleName){
            return $this->webService->sessionFlashAndRedirect(
                'The request role name arguments are required!',
                'backend/access/assignments',
                [],
                'danger'
            );
        }

        $role = $this->accessRightsService->getRoleByName($roleName);
        if (null === $role){
            return $this->webService->notFound();
        }

        return $this->viewRenderer->render('view-role', [
            'role' => $role,
            'currentUrl' => null,
        ]);
    }
}
