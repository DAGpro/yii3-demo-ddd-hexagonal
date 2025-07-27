<?php

declare(strict_types=1);

namespace App\Blog\Application\Service\AppService\QueryService;

use App\Blog\Application\Service\QueryService\ReadPostQueryServiceInterface;
use App\Blog\Domain\Port\PostRepositoryInterface;
use App\Blog\Domain\Post;
use App\Blog\Domain\Tag;
use App\Blog\Domain\User\Author;
use Cycle\ORM\Select;
use DateMalformedStringException;
use DateTimeImmutable;
use Override;
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
    #[Override]
    public function findAllPreloaded(): DataReaderInterface
    {
        $query = $this
            ->repository
            ->select()
            ->load(['tags']);

        /**
         * @psalm-return DataReaderInterface<int, Post>
         */
        return $this->prepareDataReader($query);
    }

    /**
     * Get posts without filter with preloaded Users and Tags
     *
     * @psalm-return DataReaderInterface<int, Post>
     */
    #[Override]
    public function findByTag(Tag $tag): DataReaderInterface
    {
        $query = $this
            ->repository
            ->select()
            ->load(['tags'])
            ->where(['tags.label' => $tag->getLabel()]);

        /**
         * @psalm-return DataReaderInterface<int, Post>
         */
        return $this->prepareDataReader($query);
    }

    /**
     * Get posts without filter with preloaded Users and Tags
     *
     * @psalm-return DataReaderInterface<int, Post>
     */
    #[Override]
    public function findByAuthor(Author $author): DataReaderInterface
    {
        $query = $this
            ->repository
            ->select()
            ->load(['tags'])
            ->where(['author_id' => $author->getId()]);

        /**
         * @psalm-return DataReaderInterface<int, Post>
         */
        return $this->prepareDataReader($query);
    }

    #[Override]
    public function getPostBySlug(string $slug): ?Post
    {
        /** @var Post|null $post */
        $post = $this
            ->repository
            ->select()
            ->where(['slug' => $slug])
            ->load(['tags'])
            ->fetchOne();

        return $post;
    }

    /**
     * @psalm-return Post|null
     */
    #[Override]
    public function getPost(int $id): ?Post
    {
        /** @var Post|null $post */
        $post = $this->repository
            ->select()
            ->where(['id' => $id])
            ->load(['tags'])
            ->fetchOne();

        return $post;
    }

    #[Override]
    public function fullPostPage(string $slug): ?Post
    {
        /** @var Post|null $post */
        $post = $this
            ->repository
            ->select()
            ->where(['slug' => $slug])
            ->load(['tags'])
            ->load('comments', [
                'method' => Select::OUTER_QUERY,
                'where' => ['public' => true],
            ])
            ->fetchOne();

        return $post;
    }

    /**
     * @throws DateMalformedStringException
     */
    #[Override]
    public function getMaxUpdatedAt(): DateTimeImmutable
    {
        $time = (string)($this->repository->select()->max('updated_at') ?? 'now');
        return new DateTimeImmutable($time);
    }

    /**
     * @return DataReaderInterface<int, Post>
     */
    private function prepareDataReader(Select $query): DataReaderInterface
    {
        /** @var EntityReader<int, Post> $reader */
        $reader = new EntityReader($query);

        /** @var DataReaderInterface<int, Post> $result */
        $result = $reader->withSort(
            Sort::only(['id', 'title', 'public', 'updated_at', 'published_at'])
                ->withOrder(['published_at' => 'desc']),
        );

        return $result;
    }
}
