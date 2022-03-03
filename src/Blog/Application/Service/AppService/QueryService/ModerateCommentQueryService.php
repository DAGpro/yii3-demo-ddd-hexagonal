<?php

namespace App\Blog\Application\Service\AppService\QueryService;

use App\Blog\Application\Service\QueryService\ModerateCommentQueryServiceInterface;
use App\Blog\Domain\Comment;
use App\Blog\Domain\Port\CommentRepositoryInterface;
use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Data\Reader\Sort;
use Yiisoft\Yii\Cycle\Data\Reader\EntityReader;

class ModerateCommentQueryService implements ModerateCommentQueryServiceInterface
{

    private CommentRepositoryInterface $repository;

    public function __construct(CommentRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function findAllPreloaded(): ?DataReaderInterface
    {
        $sort = $this->getSort()->withOrder(['published_at' => 'desc']);
        return $this->prepareDataReader(
            $this->repository
                ->select()
                ->scope()
                ->andWhere('deleted_at', '=', null),
            $sort
        );
    }

    public function getComment(int $commentId): ?Comment
    {
        return $this->repository->getComment($commentId);
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
