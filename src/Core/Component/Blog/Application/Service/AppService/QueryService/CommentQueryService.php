<?php

declare(strict_types=1);

namespace App\Core\Component\Blog\Application\Service\AppService\QueryService;

use App\Core\Component\Blog\Application\Service\QueryService\CommentQueryServiceInterface;
use App\Core\Component\Blog\Domain\Comment;
use App\Core\Component\Blog\Domain\Port\CommentRepositoryInterface;
use Yiisoft\Data\Paginator\KeysetPaginator;
use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Data\Reader\Sort;
use Yiisoft\Yii\Cycle\Data\Reader\EntityReader;

final class CommentQueryService implements CommentQueryServiceInterface
{
    private const COMMENTS_FEED_PER_PAGE = 10;

    private CommentRepositoryInterface $repository;

    public function __construct(CommentRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getFeedPaginator(): KeysetPaginator
    {
        $sort = $this->getSort()->withOrder(['id' => 'asc']);
        $dataReader = $this->prepareDataReader(
            $this->repository
                ->select()
                ->andWhere('deleted_at', '=', null),
            $sort
        );

        return (new KeysetPaginator($dataReader))
            ->withPageSize(self::COMMENTS_FEED_PER_PAGE);
    }

    public function findAllPreloaded(): ?DataReaderInterface
    {
        $sort = $this->getSort()->withOrder(['published_at' => 'desc']);
        return $this->prepareDataReader(
            $this->repository
                ->select()
                ->andWhere('deleted_at', '=', null),
            $sort
        );
    }

    public function getComment(int $commentId): ?Comment
    {
        return $this->repository->getComment($commentId);
    }

    public function getPublicComment(int $commentId): ?Comment
    {
        return $this->repository->getPublicComment($commentId);
    }

    private function prepareDataReader($query, Sort $sort): DataReaderInterface
    {
        return (new EntityReader($query))->withSort($sort);
    }

    private function getSort(): Sort
    {
        return Sort::only(['id', 'public', 'updated_at', 'published_at', 'post_id']);
    }
}
