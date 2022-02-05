<?php /** @noinspection DuplicatedCode */

declare(strict_types=1);

namespace App\Presentation\Frontend\Web\Component\Blog\Archive;

use App\Core\Component\Blog\Application\Service\QueryService\ArchivePostQueryServiceInterface;
use App\Core\Component\Blog\Application\Service\QueryService\TagQueryServiceInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Yiisoft\Data\Paginator\OffsetPaginator;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Yii\View\ViewRenderer;

final class ArchiveController
{
    private const POSTS_PER_PAGE = 3;
    private const POPULAR_TAGS_COUNT = 10;
    private const ARCHIVE_MONTHS_COUNT = 12;

    private ViewRenderer $view;

    public function __construct(ViewRenderer $viewRenderer)
    {
        $this->view = $viewRenderer->withControllerName('component/blog/archive');
    }

    public function index(ArchivePostQueryServiceInterface $archivePostQueryService): Response
    {
        return $this->view->render('index', [
            'archive' => $archivePostQueryService->getFullArchive()
        ]);
    }

    public function monthlyArchive(
        CurrentRoute $currentRoute,
        TagQueryServiceInterface $tagQueryService,
        ArchivePostQueryServiceInterface $archivePostQueryService
    ): Response {
        $pageNum = (int)$currentRoute->getArgument('page', '1');
        $year = (int)$currentRoute->getArgument('year', '0');
        $month = (int)$currentRoute->getArgument('month', '0');

        $dataReader = $archivePostQueryService->getMonthlyArchive($year, $month);
        $paginator = (new OffsetPaginator($dataReader))
            ->withPageSize(self::POSTS_PER_PAGE)
            ->withCurrentPage($pageNum);

        return $this->view->render(
            'monthly-archive',
            [
                'year' => $year,
                'month' => $month,
                'paginator' => $paginator,
                'archive' => $archivePostQueryService->getFullArchive(self::ARCHIVE_MONTHS_COUNT),
                'tags' => $tagQueryService->getTagMentions(self::POPULAR_TAGS_COUNT),
            ]
        );
    }

    public function yearlyArchive(
        CurrentRoute $currentRoute,
        ArchivePostQueryServiceInterface $archivePostQueryService
    ): Response {
        $year = (int)$currentRoute->getArgument('year', '0');

        $data = [
            'year' => $year,
            'items' => $archivePostQueryService->getYearlyArchive($year),
        ];
        return $this->view->render('yearly-archive', $data);
    }
}
