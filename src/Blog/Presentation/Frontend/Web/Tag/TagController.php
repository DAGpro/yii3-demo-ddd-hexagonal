<?php

declare(strict_types=1);

namespace App\Blog\Presentation\Frontend\Web\Tag;

use App\Blog\Application\Service\QueryService\ReadPostQueryServiceInterface;
use App\Blog\Application\Service\QueryService\TagQueryServiceInterface;
use App\Presentation\Infrastructure\Web\Service\WebControllerService;
use Psr\Http\Message\ResponseInterface as Response;
use Yiisoft\Data\Paginator\OffsetPaginator;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Yii\View\ViewRenderer;

final class TagController
{
    private const POSTS_PER_PAGE = 5;

    private ViewRenderer $view;
    private WebControllerService $webService;

    public function __construct(
        ViewRenderer $viewRenderer,
        WebControllerService $webControllerService
    ) {
        $this->view = $viewRenderer->withViewPath('@blogView/tag');
        $this->webService = $webControllerService;
    }

    public function index(
        CurrentRoute $currentRoute,
        TagQueryServiceInterface $tagQueryService,
        ReadPostQueryServiceInterface $postQueryService
    ): Response {
        $label = $currentRoute->getArgument('label', '');
        $pageNum = (int)$currentRoute->getArgument('page', '1');

        if (($tag = $tagQueryService->findByLabel($label)) === null) {
            return $this->webService->notFound();
        }

        $paginator = (new OffsetPaginator($postQueryService->findByTag($tag)))
            ->withPageSize(self::POSTS_PER_PAGE)
            ->withCurrentPage($pageNum);

        return $this->view->render('index', [
            'item' => $tag,
            'paginator' => $paginator,
        ]);
    }
}
