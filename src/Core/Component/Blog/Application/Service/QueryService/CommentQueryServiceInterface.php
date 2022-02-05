<?php

declare(strict_types=1);

namespace App\Core\Component\Blog\Application\Service\QueryService;

use App\Core\Component\Blog\Domain\Comment;
use Yiisoft\Data\Paginator\KeysetPaginator;
use Yiisoft\Data\Reader\DataReaderInterface;

interface CommentQueryServiceInterface
{
    public function getFeedPaginator(): KeysetPaginator;

    public function findAllPreloaded(): ?DataReaderInterface;

    public function getComment(int $commentId): ?Comment;

    public function getPublicComment(int $commentId): ?Comment;
}
