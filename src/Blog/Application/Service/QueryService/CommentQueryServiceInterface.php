<?php

declare(strict_types=1);

namespace App\Blog\Application\Service\QueryService;

use App\Blog\Domain\Comment;
use Yiisoft\Data\Paginator\KeysetPaginator;

interface CommentQueryServiceInterface
{
    public function getFeedPaginator(): KeysetPaginator;

    public function getComment(int $commentId): ?Comment;
}
