<?php

declare(strict_types=1);

namespace App\IdentityAccess\User\Slice\User\Presentation\Frontend\Web;

use App\IdentityAccess\ContextMap\AuthService\AuthenticationService;
use App\IdentityAccess\User\Domain\Exception\IdentityException;
use App\IdentityAccess\User\Slice\User\UserServiceInterface;
use App\Infrastructure\Presentation\Web\Service\WebControllerService;
use Psr\Http\Message\ResponseInterface as Response;
use Yiisoft\Yii\View\Renderer\ViewRenderer;

final readonly class CabinetController
{
    private ViewRenderer $view;

    public function __construct(
        ViewRenderer $viewRenderer,
        private WebControllerService $webService,
        private AuthenticationService $authenticationService,
    ) {
        $this->view = $viewRenderer->withViewPath(__DIR__ . '/view/cabinet');
    }

    public function index(): Response
    {
        $user = $this->authenticationService->getUser();

        return $this->view->render('index', ['item' => $user]);
    }

    public function deleteAccount(
        UserServiceInterface $userService,
    ): Response {
        try {
            $user = $this->authenticationService->getUser();
            if ($user === null || ($userId = $user->getId()) === null) {
                return $this->webService->accessDenied();
            }

            $userService->deleteUser($userId);

            return $this->webService->redirect('site/index');
        } catch (IdentityException) {
            return $this->webService->notFound();
        }
    }
}
