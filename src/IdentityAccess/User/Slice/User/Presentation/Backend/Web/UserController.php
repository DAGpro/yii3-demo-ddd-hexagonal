<?php

declare(strict_types=1);

namespace App\IdentityAccess\User\Slice\User\Presentation\Backend\Web;

use App\IdentityAccess\User\Slice\User\UserQueryServiceInterface;
use App\Infrastructure\Presentation\Web\Service\WebControllerService;
use Psr\Http\Message\ResponseInterface as Response;
use Yiisoft\Data\Paginator\OffsetPaginator;
use Yiisoft\Data\Reader\Sort;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Yii\View\Renderer\ViewRenderer;

final readonly class UserController
{
    private const int PAGINATION_INDEX = 5;

    private ViewRenderer $viewRenderer;

    public function __construct(
        ViewRenderer $viewRenderer,
        private WebControllerService $webService,
        private UserQueryServiceInterface $userQueryService,
    ) {
        $viewRenderer = $viewRenderer->withLayout('@backendLayout/main');
        $viewRenderer = $viewRenderer->withViewPath(__DIR__ . '/view');
        $this->viewRenderer = $viewRenderer->withControllerName('user');
    }

    public function index(CurrentRoute $currentRoute): Response
    {
        $pageNum = max(1, (int) $currentRoute->getArgument('page', '1'));

        $dataReader = $this->userQueryService
            ->findAllPreloaded()
            ->withSort(
                Sort::only(['login', 'id'])
                    ->withOrderString('id'),
            );

        $paginator = new OffsetPaginator($dataReader)
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
                'danger',
            );
        }

        $item = $this->userQueryService->getUser((int) $userId);
        if ($item === null) {
            return $this->webService->notFound();
        }

        return $this->viewRenderer->render('profile', ['item' => $item]);
    }
}
