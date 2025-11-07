<?php
/** @noinspection DuplicatedCode */

declare(strict_types=1);

namespace App\Blog\Presentation\Frontend\Web\Archive;

use App\Blog\Application\Service\QueryService\ArchivePostQueryServiceInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Yiisoft\Data\Paginator\OffsetPaginator;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Yii\View\Renderer\ViewRenderer;

final readonly class ArchiveController
{
    private const int POSTS_PER_PAGE = 3;

    private ViewRenderer $view;

    public function __construct(ViewRenderer $viewRenderer)
    {
        $this->view = $viewRenderer->withViewPath('@blogView/archive');
    }

    public function index(ArchivePostQueryServiceInterface $archivePostQueryService): Response
    {
        return $this->view->render('index', [
            'archive' => $archivePostQueryService->getFullArchive(),
        ]);
    }

    public function monthlyArchive(
        CurrentRoute $currentRoute,
        ArchivePostQueryServiceInterface $archivePostQueryService,
    ): Response {
        $pageNum = max(1, (int)$currentRoute->getArgument('page', '1'));
        $year = (int)$currentRoute->getArgument('year', '0');
        $month = (int)$currentRoute->getArgument('month', '0');

        $dataReader = $archivePostQueryService->getMonthlyArchive($year, $month);

        $paginator = new OffsetPaginator($dataReader)
            ->withPageSize(self::POSTS_PER_PAGE)
            ->withCurrentPage($pageNum);

        return $this->view->render(
            'monthly-archive',
            [
                'year' => $year,
                'month' => $month,
                'paginator' => $paginator,
            ],
        );
    }

    public function yearlyArchive(
        CurrentRoute $currentRoute,
        ArchivePostQueryServiceInterface $archivePostQueryService,
    ): Response {
        $year = (int)$currentRoute->getArgument('year', '0');

        $data = [
            'year' => $year,
            'items' => $archivePostQueryService->getYearlyArchive($year),
        ];
        return $this->view->render('yearly-archive', $data);
    }
}
