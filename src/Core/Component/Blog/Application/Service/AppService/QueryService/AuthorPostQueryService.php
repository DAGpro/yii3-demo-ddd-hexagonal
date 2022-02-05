<?php

declare(strict_types=1);

namespace App\Core\Component\Blog\Application\Service\AppService\QueryService;

use App\Core\Component\Blog\Application\Service\QueryService\AuthorPostQueryServiceInterface;
use App\Core\Component\Blog\Domain\Exception\BlogNotFoundException;
use App\Core\Component\Blog\Domain\Port\PostRepositoryInterface;
use App\Core\Component\Blog\Domain\Post;
use App\Core\Component\Blog\Domain\User\Author;
use Cycle\ORM\Select;
use Spiral\Database\Query\SelectQuery;
use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Data\Reader\Sort;
use Yiisoft\Yii\Cycle\Data\Reader\EntityReader;

final class AuthorPostQueryService implements AuthorPostQueryServiceInterface
{
    private PostRepositoryInterface $repository;

    public function __construct(PostRepositoryInterface $postRepository)
    {
        $this->repository = $postRepository;
    }

    /**
     * @param Author $author
     * @return DataReaderInterface
     */
    public function getAuthorPosts(Author $author): DataReaderInterface
    {
        $query = $this->repository
            ->select()
            ->scope(null)
            ->load(['tags'])
            ->where(['author_id' => $author->getId()])
            ->andWhere('deleted_at' , '=', null);

        return $this->prepareDataReader($query);
    }

    /**
     * @param string $slug
     * @return Post
     * @throws BlogNotFoundException
     */
    public function getPostBySlug(string $slug): ?Post
    {
        return $this->repository
            ->select()
            ->scope(null)
            ->load(['tags'])
            ->andWhere('slug', '=', $slug)
            ->andWhere('deleted_at' , '=', null)
            ->fetchOne();

    }

    /**
     * @psalm-suppress UndefinedDocblockClass
     *
     * @param Select|SelectQuery $query
     *
     * @return EntityReader
     */
    private function prepareDataReader($query): EntityReader
    {
        return (new EntityReader($query))
            ->withSort(
                Sort::only(['published_at'])
                    ->withOrder(['published_at' => 'desc'])
            );
    }

}
