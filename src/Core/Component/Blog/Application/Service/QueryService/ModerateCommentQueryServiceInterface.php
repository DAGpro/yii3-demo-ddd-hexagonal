<?php

namespace App\Core\Component\Blog\Application\Service\QueryService;

use App\Core\Component\Blog\Domain\Comment;
use Yiisoft\Data\Reader\DataReaderInterface;

interface ModerateCommentQueryServiceInterface
{
    public function findAllPreloaded(): ?DataReaderInterface;

    public function getComment(int $commentId): ?Comment;
}
