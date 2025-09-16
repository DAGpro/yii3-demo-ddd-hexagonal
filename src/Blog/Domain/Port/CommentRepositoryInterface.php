<?php

declare(strict_types=1);

namespace App\Blog\Domain\Port;

use App\Blog\Domain\Comment;
use Yiisoft\Data\Reader\DataReaderInterface;

interface CommentRepositoryInterface
{
    public function findAllNonDeleted(): DataReaderInterface;

    public function getPublicComment(int $commentId): ?Comment;

    public function getComment(int $commentId): ?Comment;

    /**
     * @param iterable<Comment> $comments
     */
    public function save(iterable $comments): void;

    /**
     * @param iterable<Comment> $comments
     */
    public function delete(iterable $comments): void;
}
