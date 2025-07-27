<?php

declare(strict_types=1);

namespace App\Blog\Application\Service\AppService\QueryService;

use App\Blog\Application\Service\QueryService\ArchivePostQueryServiceInterface;
use App\Blog\Domain\Port\PostRepositoryInterface;
use Cycle\ORM\Select;
use DateTimeImmutable;
use Override;
use Yiisoft\Data\Cycle\Reader\EntityReader;
use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Data\Reader\Sort;

final readonly class ArchivePostQueryService implements ArchivePostQueryServiceInterface
{
    public function __construct(private PostRepositoryInterface $repository)
    {
    }

    /**
     * @param int<0, max>|null $limit
     */
    #[Override]
    public function getFullArchive(?int $limit = null): DataReaderInterface
    {
        $sort = Sort::only(['year', 'month', 'count'])->withOrder(['year' => 'desc', 'month' => 'desc']);

        $query = $this
            ->repository
            ->select()
            ->buildQuery()
            ->columns([
                'count(id) count',
                $this->repository->extractFromDateColumn('month'),
                $this->repository->extractFromDateColumn('year'),
            ])
            ->groupBy('year, month');

        $dataReader = new EntityReader($query)->withSort($sort);

        if ($limit !== null) {
            return $dataReader->withLimit($limit);
        }

        return $dataReader;
    }

    #[Override]
    public function getMonthlyArchive(int $year, int $month): DataReaderInterface
    {
        $begin = new DateTimeImmutable()->setDate($year, $month, 1)->setTime(0, 0, 0);
        $end = $begin->setDate($year, $month + 1, 1)->setTime(0, 0, -1);

        $query = $this
            ->repository
            ->select()
            ->andWhere('published_at', 'between', $begin, $end)
            ->load(['tags']);
        return $this->prepareDataReader($query);
    }

    #[Override]
    public function getYearlyArchive(int $year): DataReaderInterface
    {
        $begin = new DateTimeImmutable()->setDate($year, 1, 1)->setTime(0, 0, 0);
        $end = $begin->setDate($year + 1, 1, 1)->setTime(0, 0, -1);

        $query = $this
            ->repository
            ->select()
            ->andWhere('published_at', 'between', $begin, $end)
            ->load('tags')
            ->orderBy(['published_at' => 'asc']);
        return $this->prepareDataReader($query);
    }

    private function prepareDataReader(Select $query): DataReaderInterface
    {
        return new EntityReader($query)
            ->withSort(
                Sort::only(['published_at'])
                    ->withOrder(['published_at' => 'desc']),
            );
    }
}
