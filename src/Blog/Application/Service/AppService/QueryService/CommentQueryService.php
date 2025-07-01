<?php

declare(strict_types=1);

namespace App\Blog\Application\Service\AppService\QueryService;

use App\Blog\Application\Service\QueryService\CommentQueryServiceInterface;
use App\Blog\Domain\Comment;
use App\Blog\Domain\Port\CommentRepositoryInterface;
use Cycle\ORM\Select;
use Yiisoft\Data\Cycle\Reader\EntityReader;
use Yiisoft\Data\Paginator\KeysetPaginator;
use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Data\Reader\Sort;

final readonly class CommentQueryService implements CommentQueryServiceInterface
{
    private const int COMMENTS_FEED_PER_PAGE = 10;

    public function __construct(private CommentRepositoryInterface $repository)
    {
    }

    #[\Override]
    public function getFeedPaginator(): KeysetPaginator
    {
        $sort = $this->getSort()->withOrder(['id' => 'asc']);
        $dataReader = $this->prepareDataReader(
            $this->repository
                ->select()
                ->andWhere('deleted_at', '=', null),
            $sort,
        );

        return new KeysetPaginator($dataReader)
            ->withPageSize(self::COMMENTS_FEED_PER_PAGE);
    }

    #[\Override]
    public function getComment(int $commentId): ?Comment
    {
        return $this->repository->getPublicComment($commentId);
    }

    private function prepareDataReader(Select $query, Sort $sort): DataReaderInterface
    {
        return new EntityReader($query)->withSort($sort);
    }

    private function getSort(): Sort
    {
        return Sort::only(['id', 'public', 'updated_at', 'published_at', 'post_id']);
    }
}
