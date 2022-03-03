<?php

declare(strict_types=1);

namespace App\Blog\Application\Service\CommandService;

use App\Blog\Domain\User\Commentator;

interface CommentServiceInterface
{

    public function add(int $postId, string $commentText, Commentator $commentator): void;

    public function edit(int $commentId, string $commentText): void;
}
