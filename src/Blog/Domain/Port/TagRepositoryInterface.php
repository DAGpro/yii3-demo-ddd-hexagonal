<?php

declare(strict_types=1);

namespace App\Blog\Domain\Port;

use App\Blog\Domain\Tag;
use Cycle\Database\Query\SelectQuery;
use Cycle\ORM\Select;

interface TagRepositoryInterface
{

    public function select(): Select;

    public function getTagMentions(): SelectQuery;

    public function getOrCreate(string $label): Tag;

    public function findByLabel(string $label): ?Tag;

    public function getTag(int $tagId): ?Tag;

    public function save(array $tags): void;

    public function delete(array $tags): void;
}
