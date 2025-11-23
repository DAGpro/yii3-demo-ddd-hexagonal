<?php

declare(strict_types=1);

namespace App\Blog\Presentation\Frontend\Web;

use App\Blog\Application\Service\QueryService\ArchivePostQueryServiceInterface;
use App\Blog\Application\Service\QueryService\ReadPostQueryServiceInterface;
use App\Blog\Application\Service\QueryService\TagQueryServiceInterface;
use App\Blog\Infrastructure\Services\IdentityAccessService;
use Psr\Http\Message\ResponseInterface as Response;
use Yiisoft\Data\Paginator\OffsetPaginator;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Yii\View\Renderer\ViewRenderer;

final readonly class BlogController
{
    private const int POSTS_PER_PAGE = 3;
    private const int POPULAR_TAGS_COUNT = 10;
    private const int ARCHIVE_MONTHS_COUNT = 12;

    private ViewRenderer $view;

    public function __construct(ViewRenderer $viewRenderer)
    {
        $this->view = $viewRenderer->withViewPath('@blogView');
    }

    public function index(
        CurrentRoute $currentRoute,
        ReadPostQueryServiceInterface $postQueryService,
        TagQueryServiceInterface $tagQueryService,
        ArchivePostQueryServiceInterface $archivePostQueryService,
        IdentityAccessService $identityAccessService,
    ): Response {
        $pageNum = max(1, (int) $currentRoute->getArgument('page', '1'));

        $dataReader = $postQueryService->findAllPreloaded();

        $paginator = new OffsetPaginator($dataReader)
            ->withPageSize(self::POSTS_PER_PAGE)
            ->withCurrentPage($pageNum);

        return $this->view->render(
            'index',
            [
                'paginator' => $paginator,
                'archive' => $archivePostQueryService->getFullArchive(self::ARCHIVE_MONTHS_COUNT),
                'tags' => $tagQueryService->getTagMentions(self::POPULAR_TAGS_COUNT),
                'author' => $identityAccessService->getAuthor(),
            ],
        );
    }
}
