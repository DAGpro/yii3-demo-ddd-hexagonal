<?php

declare(strict_types=1);

namespace App\Core\Component\Blog\Application\Service\QueryService;

use App\Core\Component\Blog\Domain\Tag;
use Yiisoft\Data\Reader\DataReaderInterface;

interface TagQueryServiceInterface
{
    public function findAllPreloaded(): DataReaderInterface;

    public function getTagMentions(?int $limit = null): DataReaderInterface;

    public function findByLabel(string $label): ?Tag;

    public function getTag(int $tagId): ?Tag;
}
