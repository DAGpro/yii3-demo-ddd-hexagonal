<?php

declare(strict_types=1);

namespace App\Blog\Application\Service\CommandService;

interface ModerateCommentServiceInterface
{

    public function draft(int $commentId): void;

    public function public(int $commentId): void;

    public function moderate(int $commentId, string $commentText, bool $public): void;

    public function delete(int $commentId): void;
}
