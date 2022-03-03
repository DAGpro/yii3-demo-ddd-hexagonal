<?php

declare(strict_types=1);

namespace App\Blog\Application\Service\AppService\QueryService;

use App\Blog\Application\Service\QueryService\ReadPostQueryServiceInterface;
use App\Blog\Domain\Port\PostRepositoryInterface;
use App\Blog\Domain\Post;
use App\Blog\Domain\Tag;
use App\Blog\Domain\User\Author;
use Cycle\ORM\Select;
use DateTimeImmutable;
use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Data\Reader\Sort;
use Yiisoft\Yii\Cycle\Data\Reader\EntityReader;

final class ReadPostQueryService implements ReadPostQueryServiceInterface
{
    private PostRepositoryInterface $repository;

    public function __construct(PostRepositoryInterface $postRepository)
    {
        $this->repository = $postRepository;
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
            ->load(['tags']);

        return $this->prepareDataReader($query);
    }

    /**
     * @param Tag $tag
     * @return DataReaderInterface
     * @psalm-return DataReaderInterface<int, Post>
     */
    public function findByTag(Tag $tag): DataReaderInterface
    {
        $query = $this
            ->repository
            ->select()
            ->load(['tags'])
            ->where(['tags.label' => $tag->getLabel()]);

        return $this->prepareDataReader($query);
    }

    public function findByAuthor(Author $author): DataReaderInterface
    {
        $query = $this
            ->repository
            ->select()
            ->load(['tags'])
            ->where(['author_id' => $author->getId()]);

        return $this->prepareDataReader($query);
    }

    public function getPostBySlug(string $slug): ?Post
    {
        return $this
            ->repository
            ->select()
            ->where(['slug' => $slug])
            ->load(['tags'])
            ->fetchOne();
    }

    public function getPost(int $id): ?Post
    {
        return $this->repository
            ->select()
            ->where(['id' => $id])
            ->load(['tags'])
            ->fetchOne();
    }

    public function fullPostPage(string $slug): ?Post
    {
        return $this
            ->repository
            ->select()
            ->where(['slug' => $slug])
            ->load(['tags'])
            ->load('comments', [
                'method' => Select::OUTER_QUERY,
                'where' => ['public' => true]
            ])
            ->fetchOne();
    }

    public function getMaxUpdatedAt(): DateTimeImmutable
    {
        return new DateTimeImmutable($this->repository->select()->max('updated_at') ?? 'now');
    }

    private function prepareDataReader($query): EntityReader
    {
        return (new EntityReader($query))->withSort(
            Sort::only(['id', 'title', 'public', 'updated_at', 'published_at'])
                ->withOrder(['published_at' => 'desc'])
        );
    }

}
