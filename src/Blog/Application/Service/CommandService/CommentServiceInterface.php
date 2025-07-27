<?php

declare(strict_types=1);

namespace App\Blog\Application\Service\CommandService;

use App\Blog\Domain\Exception\BlogNotFoundException;
use App\Blog\Domain\User\Commentator;

interface CommentServiceInterface
{

    /**
     * @throws BlogNotFoundException
     */
    public function add(int $postId, string $commentText, Commentator $commentator): void;

    /**
     * @throws BlogNotFoundException
     */
    public function edit(int $commentId, string $commentText): void;
}
