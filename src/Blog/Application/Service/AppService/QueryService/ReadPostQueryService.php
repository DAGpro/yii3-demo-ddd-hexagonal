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
use Yiisoft\Data\Cycle\Reader\EntityReader;
use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Data\Reader\Sort;

final readonly class ReadPostQueryService implements ReadPostQueryServiceInterface
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
            ->load(['tags']);

        return $this->prepareDataReader($query);
    }

    /**
     * @param Tag $tag
     * @return DataReaderInterface
     * @psalm-return DataReaderInterface<int, Post>
     */
    #[\Override]
    public function findByTag(Tag $tag): DataReaderInterface
    {
        $query = $this
            ->repository
            ->select()
            ->load(['tags'])
            ->where(['tags.label' => $tag->getLabel()]);

        return $this->prepareDataReader($query);
    }

    #[\Override]
    public function findByAuthor(Author $author): DataReaderInterface
    {
        $query = $this
            ->repository
            ->select()
            ->load(['tags'])
            ->where(['author_id' => $author->getId()]);

        return $this->prepareDataReader($query);
    }

    #[\Override]
    public function getPostBySlug(string $slug): ?Post
    {
        return $this
            ->repository
            ->select()
            ->where(['slug' => $slug])
            ->load(['tags'])
            ->fetchOne();
    }

    #[\Override]
    public function getPost(int $id): ?Post
    {
        return $this->repository
            ->select()
            ->where(['id' => $id])
            ->load(['tags'])
            ->fetchOne();
    }

    #[\Override]
    public function fullPostPage(string $slug): ?Post
    {
        return $this
            ->repository
            ->select()
            ->where(['slug' => $slug])
            ->load(['tags'])
            ->load('comments', [
                'method' => Select::OUTER_QUERY,
                'where' => ['public' => true],
            ])
            ->fetchOne();
    }

    #[\Override]
    public function getMaxUpdatedAt(): DateTimeImmutable
    {
        return new DateTimeImmutable($this->repository->select()->max('updated_at') ?? 'now');
    }

    private function prepareDataReader(Select $query): EntityReader
    {
        return new EntityReader($query)->withSort(
            Sort::only(['id', 'title', 'public', 'updated_at', 'published_at'])
                ->withOrder(['published_at' => 'desc']),
        );
    }

}
