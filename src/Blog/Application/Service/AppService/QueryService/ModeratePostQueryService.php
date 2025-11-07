<?php

declare(strict_types=1);

namespace App\Blog\Application\Service\AppService\QueryService;

use App\Blog\Application\Service\QueryService\ModeratePostQueryServiceInterface;
use App\Blog\Domain\Port\PostRepositoryInterface;
use App\Blog\Domain\Post;
use Override;
use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Data\Reader\Sort;

final readonly class ModeratePostQueryService implements ModeratePostQueryServiceInterface
{
    public function __construct(
        private PostRepositoryInterface $repository,
    ) {
    }

    /**
     * Get posts without filter with preloaded Users and Tags
     *
     * @psalm-return DataReaderInterface<int, Post>
     */
    #[Override]
    public function findAllPreloaded(): DataReaderInterface
    {
        return $this->repository
            ->getAllForModerationWithPreloadedTags()
            ->withSort(
                Sort::only(['id', 'title', 'public', 'author_name', 'public'])
                    ->withOrder(['published_at' => 'desc']),
            );
    }

    /**
     * @psalm-return Post|null
     */
    #[Override]
    public function getPost(int $id): ?Post
    {
        return $this->repository->findByIdForModeration($id);
    }
}
