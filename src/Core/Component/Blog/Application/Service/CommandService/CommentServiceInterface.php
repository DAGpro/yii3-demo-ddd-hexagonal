<?php

declare(strict_types=1);

namespace App\Core\Component\Blog\Application\Service\CommandService;

use App\Core\Component\Blog\Domain\User\Commentator;

interface CommentServiceInterface
{
    public function draft(int $commentId): void;

    public function public(int $commentId): void;

    public function moderate(int $commentId, string $commentText, bool $public): void;

    public function addComment(int $postId, string $commentText, Commentator $commentator): void;

    public function edit(int $commentId, string $commentText): void;

    public function delete(int $commentId): void;
}
