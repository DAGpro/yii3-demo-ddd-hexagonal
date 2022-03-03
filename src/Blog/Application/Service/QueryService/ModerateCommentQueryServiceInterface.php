<?php

namespace App\Blog\Application\Service\QueryService;

use App\Blog\Domain\Comment;
use Yiisoft\Data\Reader\DataReaderInterface;

interface ModerateCommentQueryServiceInterface
{
    public function findAllPreloaded(): ?DataReaderInterface;

    public function getComment(int $commentId): ?Comment;
}
