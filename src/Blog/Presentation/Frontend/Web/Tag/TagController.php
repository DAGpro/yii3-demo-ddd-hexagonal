<?php

declare(strict_types=1);

namespace App\Blog\Presentation\Frontend\Web\Tag;

use App\Blog\Application\Service\QueryService\ReadPostQueryServiceInterface;
use App\Blog\Application\Service\QueryService\TagQueryServiceInterface;
use App\Infrastructure\Presentation\Web\Service\WebControllerService;
use Psr\Http\Message\ResponseInterface as Response;
use Yiisoft\Data\Paginator\OffsetPaginator;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Yii\View\Renderer\ViewRenderer;

final readonly class TagController
{
    private const int POSTS_PER_PAGE = 5;

    private ViewRenderer $view;

    public function __construct(
        ViewRenderer $viewRenderer,
        private WebControllerService $webService,
    ) {
        $this->view = $viewRenderer->withViewPath('@blogView/tag');
    }

    public function index(
        CurrentRoute $currentRoute,
        TagQueryServiceInterface $tagQueryService,
        ReadPostQueryServiceInterface $postQueryService,
    ): Response {
        $label = $currentRoute->getArgument('label', '');
        $pageNum = (int)$currentRoute->getArgument('page', '1');

        if (($tag = $tagQueryService->findByLabel($label)) === null) {
            return $this->webService->notFound();
        }

        $paginator = new OffsetPaginator($postQueryService->findByTag($tag))
            ->withPageSize(self::POSTS_PER_PAGE)
            ->withCurrentPage($pageNum);

        return $this->view->render('index', [
            'item' => $tag,
            'paginator' => $paginator,
        ]);
    }
}
