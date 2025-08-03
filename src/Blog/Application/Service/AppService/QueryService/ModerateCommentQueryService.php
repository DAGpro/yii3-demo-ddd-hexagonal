<?php

declare(strict_types=1);

namespace App\Blog\Application\Service\AppService\QueryService;

use App\Blog\Application\Service\QueryService\ModerateCommentQueryServiceInterface;
use App\Blog\Domain\Comment;
use App\Blog\Domain\Port\CommentRepositoryInterface;
use Cycle\ORM\Select;
use Override;
use Yiisoft\Data\Cycle\Reader\EntityReader;
use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Data\Reader\Sort;

final readonly class ModerateCommentQueryService implements ModerateCommentQueryServiceInterface
{
    public function __construct(private CommentRepositoryInterface $repository)
    {
    }

    #[Override]
    public function findAllPreloaded(): DataReaderInterface
    {
        $sort = $this->getSort()->withOrder(['published_at' => 'desc']);
        return $this->prepareDataReader(
            $this->repository
                ->select()
                ->andWhere('deleted_at', '=', null),
            $sort,
        );
    }

    #[Override]
    public function getComment(int $commentId): ?Comment
    {
        return $this->repository->getComment($commentId);
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
