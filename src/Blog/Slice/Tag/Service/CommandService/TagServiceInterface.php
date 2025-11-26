<?php

declare(strict_types=1);

namespace App\Blog\Slice\Tag\Service\CommandService;

interface TagServiceInterface
{
    public function changeTag(int $tagId, string $tagLabel): void;

    public function delete(int $tagId): void;
}
