<?php

declare(strict_types=1);

namespace App\Blog\Slice\Tag\Service\QueryService;

use App\Blog\Domain\Tag;
use Yiisoft\Data\Reader\DataReaderInterface;

interface TagQueryServiceInterface
{
    public function findAllPreloaded(): DataReaderInterface;

    /**
     * @param int<0,max>|null $limit
     **/
    public function getTagMentions(?int $limit = null): DataReaderInterface;

    public function findByLabel(string $label): ?Tag;

    public function getTag(int $tagId): ?Tag;
}
