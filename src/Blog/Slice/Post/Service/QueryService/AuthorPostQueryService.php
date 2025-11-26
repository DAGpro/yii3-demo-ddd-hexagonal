<?php

declare(strict_types=1);

namespace App\Blog\Slice\Post\Service\QueryService;

use App\Blog\Domain\Port\PostRepositoryInterface;
use App\Blog\Domain\Post;
use App\Blog\Domain\User\Author;
use Override;
use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Data\Reader\Sort;

final readonly class AuthorPostQueryService implements AuthorPostQueryServiceInterface
{
    public function __construct(
        private PostRepositoryInterface $repository,
    ) {}

    /**
     * @param Author $author
     * @return DataReaderInterface
     */
    #[Override]
    public function getAuthorPosts(Author $author): DataReaderInterface
    {
        return $this->repository
            ->findByAuthorNotDeletedPostWithPreloadedTags($author)
            ->withSort(
                Sort::only(['published_at'])
                    ->withOrder(['published_at' => 'desc']),
            );
    }

    #[Override]
    public function getPostBySlug(string $slug): ?Post
    {
        return $this->repository->findBySlugNotDeletedPostWithPreloadedTags($slug);
    }
}
