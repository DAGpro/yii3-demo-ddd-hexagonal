<?php

declare(strict_types=1);

namespace App\Blog\Domain\Port;

use App\Blog\Domain\Tag;
use Yiisoft\Data\Reader\DataReaderInterface;

interface TagRepositoryInterface
{
    public function findAllPreloaded(): DataReaderInterface;

    public function getTagMentions(): DataReaderInterface;

    public function getOrCreate(string $label): Tag;

    public function findByLabel(string $label): ?Tag;

    public function getTag(int $tagId): ?Tag;

    public function save(array $tags): void;

    public function delete(array $tags): void;
}
