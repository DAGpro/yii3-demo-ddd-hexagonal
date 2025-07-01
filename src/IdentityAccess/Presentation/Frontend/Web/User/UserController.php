<?php

declare(strict_types=1);

namespace App\IdentityAccess\Presentation\Frontend\Web\User;

use App\IdentityAccess\User\Application\Service\UserQueryServiceInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Data\Paginator\OffsetPaginator;
use Yiisoft\Data\Reader\Sort;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Yii\View\Renderer\ViewRenderer;

final class UserController
{
    private const int PAGINATION_INDEX = 5;

    public function __construct(private ViewRenderer $viewRenderer)
    {
        $this->viewRenderer = $viewRenderer->withViewPath('@identityView/user/user');
    }

    public function index(
        UserQueryServiceInterface $userQueryService,
        ServerRequestInterface $request,
        CurrentRoute $currentRoute,
    ): Response {
        $page = (int)$currentRoute->getArgument('page', '1');
        $sortOrderString = $request->getQueryParams();

        $dataReader = $userQueryService
            ->findAllPreloaded()
            ->withSort(
                Sort::only(['id', 'login'])
                    ->withOrderString($sortOrderString['sort'] ?? ''),
            );

        $paginator = new OffsetPaginator($dataReader)
            ->withPageSize(self::PAGINATION_INDEX);

        return $this->viewRenderer->render(
            'index',
            [
                'currentRoute' => $currentRoute,
                'paginator' => $paginator,
            ],
        );
    }

    public function profile(
        CurrentRoute $currentRoute,
        UserQueryServiceInterface $userQueryService,
        ResponseFactoryInterface $responseFactory,
    ): Response {
        $login = $currentRoute->getArgument('login');
        $item = $userQueryService->findByLogin($login);
        if ($item === null) {
            return $responseFactory->createResponse(404);
        }

        return $this->viewRenderer->render('profile', ['item' => $item]);
    }
}
