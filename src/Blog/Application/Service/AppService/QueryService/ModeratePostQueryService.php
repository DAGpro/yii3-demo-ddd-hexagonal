<?php

declare(strict_types=1);

namespace App\Blog\Application\Service\AppService\QueryService;

use App\Blog\Application\Service\QueryService\ModeratePostQueryServiceInterface;
use App\Blog\Domain\Port\PostRepositoryInterface;
use App\Blog\Domain\Post;
use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Data\Reader\Sort;
use Yiisoft\Yii\Cycle\Data\Reader\EntityReader;

final class ModeratePostQueryService implements ModeratePostQueryServiceInterface
{
    private PostRepositoryInterface $repository;

    public function __construct(PostRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get posts without filter with preloaded Users and Tags
     *
     * @psalm-return DataReaderInterface<int, Post>
     */
    public function findAllPreloaded(): DataReaderInterface
    {
        $query = $this
            ->repository
            ->select()
            ->scope()
            ->andWhere('deleted_at', '=', null)
            ->load(['tags']);

        return $this->prepareDataReader($query);
    }

    public function getPost(int $id): ?Post
    {
        return $this
            ->repository
            ->select()
            ->scope()
            ->where('id', '=', $id)
            ->andWhere('deleted_at', '=', null)
            ->fetchOne();
    }

    private function prepareDataReader($query): DataReaderInterface
    {
        return (new EntityReader($query))->withSort(
            Sort::only(['id', 'title', 'public', 'updated_at', 'published_at'])
                ->withOrder(['published_at' => 'desc'])
        );
    }
}
