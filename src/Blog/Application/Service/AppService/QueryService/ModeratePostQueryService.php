<?php

declare(strict_types=1);

namespace App\Blog\Application\Service\AppService\QueryService;

use App\Blog\Application\Service\QueryService\ModeratePostQueryServiceInterface;
use App\Blog\Domain\Port\PostRepositoryInterface;
use App\Blog\Domain\Post;
use Cycle\ORM\Select;
use Yiisoft\Data\Cycle\Reader\EntityReader;
use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Data\Reader\Sort;

final readonly class ModeratePostQueryService implements ModeratePostQueryServiceInterface
{
    public function __construct(private PostRepositoryInterface $repository)
    {
    }

    /**
     * Get posts without filter with preloaded Users and Tags
     *
     * @psalm-return DataReaderInterface<int, Post>
     */
    #[\Override]
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

    #[\Override]
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

    private function prepareDataReader(Select $query): DataReaderInterface
    {
        return new EntityReader($query)->withSort(
            Sort::only(['id', 'title', 'public', 'updated_at', 'published_at'])
                ->withOrder(['published_at' => 'desc']),
        );
    }
}
