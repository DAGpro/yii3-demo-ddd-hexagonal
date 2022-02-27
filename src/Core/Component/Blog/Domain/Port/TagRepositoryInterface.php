<?php

declare(strict_types=1);

namespace App\Core\Component\Blog\Domain\Port;

use App\Core\Component\Blog\Domain\Tag;
use Cycle\ORM\Select;
use Cycle\Database\Query\SelectQuery;

interface TagRepositoryInterface
{

    public function select(): Select;

    public function getTagMentions(int $limit = 0): SelectQuery;

    public function getOrCreate(string $label): Tag;

    public function findByLabel(string $label): ?Tag;

    public function getTag(int $tagId): ?Tag;

    public function save(array $tags): void;

    public function delete(array $tags): void;
}
