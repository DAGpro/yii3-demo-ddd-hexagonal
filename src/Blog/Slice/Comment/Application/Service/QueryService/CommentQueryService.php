<?php

declare(strict_types=1);

namespace App\Blog\Slice\Comment\Application\Service\QueryService;

use App\Blog\Domain\Comment;
use App\Blog\Domain\Port\CommentRepositoryInterface;
use Override;
use Yiisoft\Data\Paginator\KeysetPaginator;
use Yiisoft\Data\Reader\Sort;

final readonly class CommentQueryService implements CommentQueryServiceInterface
{
    private const int COMMENTS_FEED_PER_PAGE = 20;

    public function __construct(
        private CommentRepositoryInterface $repository,
    ) {}

    #[Override]
    public function getFeedPaginator(): KeysetPaginator
    {
        $dataReader = $this->repository
            ->findAllNonDeleted()
            ->withSort(
                Sort::only(['id', 'public', 'updated_at', 'published_at', 'post_id'])
                    ->withOrder(['id' => 'desc']),
            );

        return new KeysetPaginator($dataReader)
            ->withPageSize(self::COMMENTS_FEED_PER_PAGE);
    }

    #[Override]
    public function getComment(int $commentId): ?Comment
    {
        return $this->repository->getPublicComment($commentId);
    }
}
