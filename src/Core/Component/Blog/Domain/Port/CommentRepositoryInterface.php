<?php

declare(strict_types=1);

namespace App\Core\Component\Blog\Domain\Port;

use App\Core\Component\Blog\Domain\Comment;
use Cycle\ORM\Select;

interface CommentRepositoryInterface
{

    public function select(): Select;

    public function getPublicComment(int $commentId): ?Comment;

    public function getComment(int $commentId): ?Comment;

    public function save(array $comments): void;

    public function delete(array $comments): void;
}
