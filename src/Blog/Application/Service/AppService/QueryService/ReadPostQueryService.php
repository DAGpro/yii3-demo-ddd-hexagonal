<?php

declare(strict_types=1);

namespace App\Blog\Application\Service\AppService\QueryService;

use App\Blog\Application\Service\QueryService\ReadPostQueryServiceInterface;
use App\Blog\Domain\Port\PostRepositoryInterface;
use App\Blog\Domain\Post;
use App\Blog\Domain\Tag;
use DateMalformedStringException;
use DateTimeImmutable;
use Exception;
use Override;
use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Data\Reader\Sort;

final readonly class ReadPostQueryService implements ReadPostQueryServiceInterface
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
            ->getAllWithPreloadedTags()
            ->withSort(
                Sort::only(['id', 'title', 'public', 'updated_at', 'published_at'])
                    ->withOrder(['published_at' => 'desc']),
            );
    }

    /**
     * Get posts without filter with preloaded Users and Tags
     *
     * @psalm-return DataReaderInterface<int, Post>
     */
    #[Override]
    public function findByTag(Tag $tag): DataReaderInterface
    {
        return $this->repository
            ->findByTagWithPreloadedTags($tag)
            ->withSort(
                Sort::only(['id', 'title', 'public', 'updated_at', 'published_at'])
                    ->withOrder(['published_at' => 'desc']),
            );
    }

    #[Override]
    public function getPostBySlug(string $slug): ?Post
    {
        return $this->repository->findBySlug($slug);
    }

    /**
     * @psalm-return Post|null
     */
    #[Override]
    public function getPost(int $id): ?Post
    {
        return $this->repository->findById($id);
    }

    #[Override]
    public function fullPostPage(string $slug): ?Post
    {
        return $this->repository->fullPostBySlug($slug);
    }

    /**
     * @throws DateMalformedStringException
     * @throws Exception
     */
    #[Override]
    public function getMaxUpdatedAt(): DateTimeImmutable
    {
        return $this->repository->getMaxUpdatedAt();
    }
}
