<?php

namespace App\Core\Component\Blog\Application\Service\CommandService;

interface ModerateCommentServiceInterface
{

    public function draft(int $commentId): void;

    public function public(int $commentId): void;

    public function moderate(int $commentId, string $commentText, bool $public): void;

    public function delete(int $commentId): void;
}
