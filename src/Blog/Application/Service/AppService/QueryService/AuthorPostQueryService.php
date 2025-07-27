<?php

declare(strict_types=1);

namespace App\Blog\Application\Service\AppService\QueryService;

use App\Blog\Application\Service\QueryService\AuthorPostQueryServiceInterface;
use App\Blog\Domain\Port\PostRepositoryInterface;
use App\Blog\Domain\Post;
use App\Blog\Domain\User\Author;
use Cycle\ORM\Select;
use Override;
use Yiisoft\Data\Cycle\Reader\EntityReader;
use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Data\Reader\Sort;

final readonly class AuthorPostQueryService implements AuthorPostQueryServiceInterface
{
    public function __construct(private PostRepositoryInterface $repository)
    {
    }

    /**
     * @param Author $author
     * @return DataReaderInterface
     */
    #[Override]
    public function getAuthorPosts(Author $author): DataReaderInterface
    {
        $query = $this->repository
            ->select()
            ->scope(null)
            ->load(['tags'])
            ->where(['author_id' => $author->getId()])
            ->andWhere('deleted_at', '=', null);

        return $this->prepareDataReader($query);
    }

    #[Override]
    public function getPostBySlug(string $slug): ?Post
    {
        /** @var Post|null $post */
        $post = $this->repository
            ->select()
            ->scope(null)
            ->load(['tags'])
            ->andWhere('slug', '=', $slug)
            ->andWhere('deleted_at', '=', null)
            ->fetchOne();

        return $post;
    }

    private function prepareDataReader(Select $query): DataReaderInterface
    {
        return new EntityReader($query)
            ->withSort(
                Sort::only(['published_at'])
                    ->withOrder(['published_at' => 'desc']),
            );
    }
}
