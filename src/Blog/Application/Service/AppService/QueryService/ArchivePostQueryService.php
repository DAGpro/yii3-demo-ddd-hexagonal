<?php

declare(strict_types=1);

namespace App\Blog\Application\Service\AppService\QueryService;

use App\Blog\Application\Service\QueryService\ArchivePostQueryServiceInterface;
use App\Blog\Domain\Port\PostRepositoryInterface;
use Cycle\ORM\Select;
use Cycle\Database\Query\SelectQuery;
use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Data\Reader\Sort;
use Yiisoft\Yii\Cycle\Data\Reader\EntityReader;

final class ArchivePostQueryService implements ArchivePostQueryServiceInterface
{
    private PostRepositoryInterface $repository;

    public function __construct(PostRepositoryInterface $postRepository)
    {
        $this->repository = $postRepository;
    }

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

        $dataReader = (new EntityReader($query))->withSort($sort);

        if ($limit !== null) {
            return $dataReader->withLimit($limit);
        }

        return $dataReader;
    }

    public function getMonthlyArchive(int $year, int $month): DataReaderInterface
    {
        $begin = (new \DateTimeImmutable())->setDate($year, $month, 1)->setTime(0, 0, 0);
        $end = $begin->setDate($year, $month + 1, 1)->setTime(0, 0, -1);

        $query = $this
            ->repository
            ->select()
            ->andWhere('published_at', 'between', $begin, $end)
            ->load(['tags']);
        return $this->prepareDataReader($query);
    }

    public function getYearlyArchive(int $year): DataReaderInterface
    {
        $begin = (new \DateTimeImmutable())->setDate($year, 1, 1)->setTime(0, 0, 0);
        $end = $begin->setDate($year + 1, 1, 1)->setTime(0, 0, -1);

        $query = $this
            ->repository
            ->select()
            ->andWhere('published_at', 'between', $begin, $end)
            ->load('tags')
            ->orderBy(['published_at' => 'asc']);
        return $this->prepareDataReader($query);
    }

    /**
     * @psalm-suppress UndefinedDocblockClass
     *
     * @param Select|SelectQuery $query
     *
     * @return EntityReader
     */
    private function prepareDataReader($query): EntityReader
    {
        return (new EntityReader($query))
            ->withSort(
                Sort::only(['published_at'])
                ->withOrder(['published_at' => 'desc'])
            );
    }
}
