<?php

declare(strict_types=1);

namespace App\Blog\Slice\Comment\Application\Service\QueryService;

use App\Blog\Domain\Comment;
use App\Blog\Domain\Port\CommentRepositoryInterface;
use Override;
use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Data\Reader\Sort;

final readonly class ModerateCommentQueryService implements ModerateCommentQueryServiceInterface
{
    public function __construct(
        private CommentRepositoryInterface $repository,
    ) {}

    #[Override]
    public function findAllPreloaded(): DataReaderInterface
    {
        return $this->repository
            ->findAllNonDeleted()
            ->withSort(
                Sort::only(['id', 'public', 'updated_at', 'published_at', 'post_id'])
                    ->withOrder(['id' => 'desc']),
            );
    }

    #[Override]
    public function getComment(int $commentId): ?Comment
    {
        return $this->repository->getComment($commentId);
    }
}
