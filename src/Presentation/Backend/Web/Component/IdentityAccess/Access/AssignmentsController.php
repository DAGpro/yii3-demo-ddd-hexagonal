<?php

declare(strict_types=1);

namespace App\Presentation\Backend\Web\Component\IdentityAccess\Access;

use App\IdentityAccess\Access\Application\Service\AssignmentsServiceInterface;
use App\IdentityAccess\User\Application\Service\UserQueryServiceInterface;
use App\IdentityAccess\User\Domain\Exception\IdentityException;
use App\Presentation\Infrastructure\Web\Service\WebControllerService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Yii\View\ViewRenderer;

class AssignmentsController
{
    private ViewRenderer $viewRenderer;
    private WebControllerService $webService;
    private AssignmentsServiceInterface $assignmentsService;
    private UserQueryServiceInterface $userQueryService;

    public function __construct(
        ViewRenderer $viewRenderer,
        WebControllerService $webService,
        UserQueryServiceInterface $userQueryService,
        AssignmentsServiceInterface $assignmentsService,
    ) {
        $this->webService = $webService;
        $this->assignmentsService = $assignmentsService;
        $this->userQueryService = $userQueryService;
        $viewRenderer = $viewRenderer->withLayout('@backendLayout/main');
        $viewRenderer = $viewRenderer->withViewPath('@backendView/component/identity-access/access');
        $this->viewRenderer = $viewRenderer->withControllerName('assignments');
    }

    public function assignments(): ResponseInterface
    {
        $usersAssignments = $this->assignmentsService->getAssignments();

        return $this->viewRenderer->render('assignments', [
            'users' => $usersAssignments,
            'currentUrl' => 'assignments'
        ]);

    }

    public function userAssignments(Request $request, CurrentRoute $currentRoute): ResponseInterface
    {
        $userId = $currentRoute->getArgument('user_id');
        if ($userId === null) {
            return $this->webService->sessionFlashAndRedirect(
                'The request must have a user_id argument',
                'backend/access/assignments',
                [], 'danger'
            );
        }

        try {
            $user = $this->userQueryService->getUser((int)$userId);
            if ($user === null) {
                throw new IdentityException('User is not found!');
            }

            $userWithAssignments = $this->assignmentsService->getUserAssignments($user);

            return $this->viewRenderer->render('user-assignments', [
                'user' => $userWithAssignments,
                'currentUrl' => null,
            ]);
        } catch (IdentityException $exception) {
            return $this->webService->notFound();
        }
    }
}
