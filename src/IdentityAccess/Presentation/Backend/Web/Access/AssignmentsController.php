<?php

declare(strict_types=1);

namespace App\IdentityAccess\Presentation\Backend\Web\Access;

use App\IdentityAccess\Access\Application\Service\AssignmentsServiceInterface;
use App\IdentityAccess\User\Application\Service\UserQueryServiceInterface;
use App\IdentityAccess\User\Domain\Exception\IdentityException;
use App\Infrastructure\Presentation\Web\Service\WebControllerService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Yii\View\Renderer\ViewRenderer;

final readonly class AssignmentsController
{
    private ViewRenderer $viewRenderer;

    public function __construct(
        ViewRenderer $viewRenderer,
        private WebControllerService $webService,
        private UserQueryServiceInterface $userQueryService,
        private AssignmentsServiceInterface $assignmentsService,
    ) {
        $viewRenderer = $viewRenderer->withLayout('@backendLayout/main');
        $viewRenderer = $viewRenderer->withViewPath('@identityBackendView/access');
        $this->viewRenderer = $viewRenderer->withControllerName('assignments');
    }

    public function assignments(): ResponseInterface
    {
        $usersAssignments = $this->assignmentsService->getAssignments();

        return $this->viewRenderer->render('assignments', [
            'users' => $usersAssignments,
            'currentUrl' => 'assignments',
        ]);

    }

    public function userAssignments(Request $request, CurrentRoute $currentRoute): ResponseInterface
    {
        $userId = $currentRoute->getArgument('user_id');
        if ($userId === null) {
            return $this->webService->sessionFlashAndRedirect(
                'The request must have a user_id argument',
                'backend/access/assignments',
                [],
                'danger',
            );
        }

        try {
            $user = $this->userQueryService->getUser((int) $userId);
            if ($user === null) {
                throw new IdentityException('User is not found!');
            }

            $userWithAssignments = $this->assignmentsService->getUserAssignments($user);

            return $this->viewRenderer->render('user-assignments', [
                'user' => $userWithAssignments,
                'currentUrl' => null,
            ]);
        } catch (IdentityException) {
            return $this->webService->notFound();
        }
    }
}
