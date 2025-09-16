<?php

declare(strict_types=1);

namespace App\Blog\Application\Service\AppService\QueryService;

use App\Blog\Application\Service\QueryService\ArchivePostQueryServiceInterface;
use App\Blog\Domain\Port\PostRepositoryInterface;
use Override;
use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Data\Reader\Sort;

final readonly class ArchivePostQueryService implements ArchivePostQueryServiceInterface
{
    public function __construct(
        private PostRepositoryInterface $repository,
    ) {
    }

    /**
     * @param int<0, max>|null $limit
     */
    #[Override]
    public function getFullArchive(?int $limit = null): DataReaderInterface
    {
        $dataReader = $this->repository
            ->getFullArchive()
            ->withSort(
                Sort::only(['year', 'month', 'count'])
                    ->withOrder(['year' => 'desc', 'month' => 'desc']),
            );

        if ($limit !== null) {
            return $dataReader->withLimit($limit);
        }

        return $dataReader;
    }

    #[Override]
    public function getMonthlyArchive(int $year, int $month): DataReaderInterface
    {
        return $this->repository->getMonthlyArchive($year, $month);
    }

    #[Override]
    public function getYearlyArchive(int $year): DataReaderInterface
    {
        return $this->repository->getYearlyArchive($year);
    }
}
