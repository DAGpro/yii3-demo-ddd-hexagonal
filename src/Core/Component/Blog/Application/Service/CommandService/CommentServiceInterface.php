<?php

declare(strict_types=1);

namespace App\Core\Component\Blog\Application\Service\CommandService;

use App\Core\Component\Blog\Domain\User\Commentator;

interface CommentServiceInterface
{

    public function add(int $postId, string $commentText, Commentator $commentator): void;

    public function edit(int $commentId, string $commentText): void;
}
