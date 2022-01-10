<?php

declare(strict_types=1);

namespace App\Presentation\Frontend\Web\Component\IdentityAccess\User;

use App\Core\Component\IdentityAccess\User\Application\Service\UserQueryServiceInterface;
use App\Core\Component\IdentityAccess\User\Infrastructure\Persistence\UserRepository;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Yiisoft\Data\Paginator\OffsetPaginator;
use Yiisoft\Data\Reader\Sort;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Yii\View\ViewRenderer;

final class UserController
{
    private const PAGINATION_INDEX = 5;

    private ViewRenderer $viewRenderer;

    public function __construct(ViewRenderer $viewRenderer)
    {
        $this->viewRenderer = $viewRenderer->withControllerName('component/identity-access/user/user');
    }

    public function index(UserQueryServiceInterface $userQueryService, CurrentRoute $currentRoute): Response
    {
        $pageNum = (int)$currentRoute->getArgument('page', '1');

        $dataReader = $userQueryService
            ->findAllPreloaded()
            ->withSort(
                Sort::only(['login'])
                ->withOrderString('login')
            );

        $paginator = (new OffsetPaginator($dataReader))
            ->withPageSize(self::PAGINATION_INDEX)
            ->withCurrentPage($pageNum);

        return $this->viewRenderer->render('index', ['paginator' => $paginator]);
    }

    public function profile(
        CurrentRoute $currentRoute,
        UserQueryServiceInterface $userQueryService,
        ResponseFactoryInterface $responseFactory
    ): Response {
        $login = $currentRoute->getArgument('login');
        $item = $userQueryService->findByLogin($login);
        if ($item === null) {
            return $responseFactory->createResponse(404);
        }

        return $this->viewRenderer->render('profile', ['item' => $item]);
    }
}
