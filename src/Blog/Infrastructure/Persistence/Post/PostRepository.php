<?php

declare(strict_types=1);

namespace App\Blog\Infrastructure\Persistence\Post;

use App\Blog\Domain\Port\PostRepositoryInterface;
use App\Blog\Domain\Post;
use App\Blog\Domain\Tag;
use App\Blog\Domain\User\Author;
use Cycle\Database\DatabaseInterface;
use Cycle\Database\Driver\DriverInterface;
use Cycle\Database\Driver\SQLite\SQLiteDriver;
use Cycle\Database\Injection\Fragment;
use Cycle\Database\Injection\FragmentInterface;
use Cycle\ORM\EntityManagerInterface;
use Cycle\ORM\Select;
use DateMalformedStringException;
use DateTimeImmutable;
use Override;
use Yiisoft\Data\Cycle\Reader\EntityReader;
use Yiisoft\Data\Reader\DataReaderInterface;

/**
 * @extends Select\Repository<Post>
 */
final class PostRepository extends Select\Repository implements PostRepositoryInterface
{
    /**
     * @param Select<Post> $select
     */
    public function __construct(
        protected Select $select,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct($select);
    }

    // Archive methods

    #[Override]
    public function getFullArchive(): DataReaderInterface
    {
        $query = $this
            ->select()
            ->buildQuery()
            ->columns([
                'count(id) count',
                $this->extractFromDateColumn('month'),
                $this->extractFromDateColumn('year'),
            ])
            ->groupBy('year, month');

        /** @var DataReaderInterface<int, Post> $reader */
        $reader = new EntityReader($query);
        return $reader;
    }

    #[Override]
    public function getMonthlyArchive(int $year, int $month): DataReaderInterface
    {
        $begin = new DateTimeImmutable()
            ->setDate($year, $month, 1)
            ->setTime(0, 0);
        $end = $begin
            ->setDate($year, $month + 1, 1)
            ->setTime(0, 0, -1);

        $query = $this
            ->select()
            ->andWhere('published_at', 'between', $begin, $end)
            ->load(['tags']);

        /** @var DataReaderInterface<int, Post> $reader */
        $reader = new EntityReader($query);
        return $reader;
    }

    #[Override]
    public function getYearlyArchive(int $year): DataReaderInterface
    {
        $begin = new DateTimeImmutable()
            ->setDate($year, 1, 1)
            ->setTime(0, 0);
        $end = $begin
            ->setDate($year + 1, 1, 1)
            ->setTime(0, 0, -1);

        $query = $this
            ->select()
            ->andWhere('published_at', 'between', $begin, $end)
            ->load('tags')
            ->orderBy(['published_at' => 'asc']);

        /** @var DataReaderInterface<int, Post> $reader */
        $reader = new EntityReader($query);
        return $reader;
    }

    #[Override]
    public function getAllWithPreloadedTags(): DataReaderInterface
    {
        $query = $this->select()->load(['tags']);

        /** @var DataReaderInterface<int, Post> $reader */
        $reader = new EntityReader($query);
        return $reader;
    }

    #[Override]
    public function findByTagWithPreloadedTags(Tag $tag): DataReaderInterface
    {
        $query = $this
            ->select()
            ->load(['tags'])
            ->where(['tags.label' => $tag->getLabel()]);

        /** @var DataReaderInterface<int, Post> $reader */
        $reader = new EntityReader($query);
        return $reader;
    }

    #[Override]
    public function findByAuthorNotDeletedPostWithPreloadedTags(Author $author): DataReaderInterface
    {
        $query = $this
            ->select()
            ->scope()
            ->load(['tags'])
            ->where('author_id', $author->getId())
            ->andWhere('deleted_at', null);

        /** @var DataReaderInterface<int, Post> $reader */
        $reader = new EntityReader($query);
        return $reader;
    }

    #[Override]
    public function getAllForModerationWithPreloadedTags(): DataReaderInterface
    {
        $query = $this
            ->select()
            ->scope()
            ->andWhere('deleted_at', '=', null)
            ->load(['tags']);

        /** @var DataReaderInterface<int, Post> $reader */
        $reader = new EntityReader($query);
        return $reader;
    }

    #[Override]
    public function findBySlug(string $slug): ?Post
    {
        return $this
            ->select()
            ->where(['slug' => $slug])
            ->fetchOne();
    }

    #[Override]
    public function findBySlugNotDeletedPostWithPreloadedTags(string $slug): ?Post
    {
        return $this
            ->select()
            ->scope()
            ->load(['tags'])
            ->andWhere('slug', '=', $slug)
            ->andWhere('deleted_at', '=', null)
            ->fetchOne();
    }

    #[Override]
    public function fullPostBySlug(string $slug): ?Post
    {
        return $this
            ->select()
            ->where(['slug' => $slug])
            ->load(['tags'])
            ->load('comments', [
                'method' => Select::OUTER_QUERY,
            ])
            ->fetchOne();
    }

    #[Override]
    public function findById(int $id): ?Post
    {
        return $this
            ->select()
            ->where(['id' => $id])
            ->fetchOne();
    }

    #[Override]
    public function findByIdForModeration(int $id): ?Post
    {
        return $this
            ->select()
            ->scope()
            ->where('id', '=', $id)
            ->andWhere('deleted_at', '=', null)
            ->fetchOne();
    }

    /**
     * @throws DateMalformedStringException
     */
    #[Override]
    public function getMaxUpdatedAt(): DateTimeImmutable
    {
        $time = (string)($this->select()->max('updated_at') ?? 'now');

        return new DateTimeImmutable($time);
    }

    /**
     * @param iterable<Post> $posts
     */
    #[Override]
    public function save(iterable $posts): void
    {
        if ($posts === []) {
            return;
        }

        foreach ($posts as $entity) {
            $this->entityManager->persist($entity);
        }
        $this->entityManager->run();
    }

    /**
     * @param iterable<Post> $posts
     */
    #[Override]
    public function delete(iterable $posts): void
    {
        if ($posts === []) {
            return;
        }

        foreach ($posts as $post) {
            $this->entityManager->delete($post);
        }
        $this->entityManager->run();
    }

    /**
     * @param 'day'|'month'|'year' $attr
     * @return FragmentInterface
     */
    private function extractFromDateColumn(string $attr): FragmentInterface
    {
        $driver = $this->getDriver();
        $wrappedField = $driver->getQueryCompiler()->quoteIdentifier($attr);

        if ($driver instanceof SQLiteDriver) {
            $formatMap = [
                'year' => '%Y',
                'month' => '%m',
                'day' => '%d',
            ];
            $str = $formatMap[$attr] ?? '%Y';
            return new Fragment("strftime('$str', published_at) $wrappedField");
        }

        return new Fragment("extract($attr from published_at) $wrappedField");
    }

    private function getDriver(): DriverInterface
    {
        return $this
            ->select()
            ->getBuilder()
            ->getLoader()
            /** @psalm-suppress InternalMethod */
            ->getSource()
            ->getDatabase()
            ->getDriver(DatabaseInterface::READ);
    }
}
