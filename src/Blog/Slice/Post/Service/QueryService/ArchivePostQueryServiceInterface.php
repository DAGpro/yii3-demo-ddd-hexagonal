<?php

declare(strict_types=1);

namespace App\Blog\Slice\Post\Service\QueryService;

use Yiisoft\Data\Reader\DataReaderInterface;

interface ArchivePostQueryServiceInterface
{
    /**
     * @param int<0, max>|null $limit
     */
    public function getFullArchive(?int $limit = null): DataReaderInterface;

    public function getMonthlyArchive(int $year, int $month): DataReaderInterface;

    public function getYearlyArchive(int $year): DataReaderInterface;
}
