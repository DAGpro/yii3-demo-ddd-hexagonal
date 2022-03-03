<?php

declare(strict_types=1);

namespace App\Presentation\Backend\Web\Component\IdentityAccess\User;

use App\IdentityAccess\User\Application\Service\UserQueryServiceInterface;
use App\Presentation\Infrastructure\Web\Service\WebControllerService;
use Psr\Http\Message\ResponseInterface as Response;
use Yiisoft\Data\Paginator\OffsetPaginator;
use Yiisoft\Data\Reader\Sort;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Yii\View\ViewRenderer;

class UserController
{
    private const PAGINATION_INDEX = 5;

    private ViewRenderer $viewRenderer;
    private WebControllerService $webService;
    private UserQueryServiceInterface $userQueryService;

    public function __construct(
        ViewRenderer $viewRenderer,
        WebControllerService $webService,
        UserQueryServiceInterface $userQueryService
    ) {
        $viewRenderer = $viewRenderer->withLayout('@backendLayout/main');
        $viewRenderer = $viewRenderer->withViewPath('@backendView/component/identity-access/user');
        $this->viewRenderer = $viewRenderer->withControllerName('user');
        $this->webService = $webService;
        $this->userQueryService = $userQueryService;
    }

    public function index(CurrentRoute $currentRoute): Response
    {
        $pageNum = (int)$currentRoute->getArgument('page', '1');

        $dataReader = $this->userQueryService
            ->findAllPreloaded()
            ->withSort(Sort::only(['login'])
            ->withOrderString('login'));

        $paginator = (new OffsetPaginator($dataReader))
            ->withPageSize(self::PAGINATION_INDEX)
            ->withCurrentPage($pageNum);

        return $this->viewRenderer->render('index', ['paginator' => $paginator]);
    }

    public function profile(CurrentRoute $currentRoute): Response
    {
        $userId = $currentRoute->getArgument('user_id');
        if ($userId === null) {
            return $this->webService->sessionFlashAndRedirect(
                'User ID not specified',
                'backend/user',
                [],
                'danger'
            );
        }

        $item = $this->userQueryService->getUser((int)$userId);
        if ($item === null) {
            return $this->webService->notFound();
        }

        return $this->viewRenderer->render('profile', ['item' => $item]);
    }
}
