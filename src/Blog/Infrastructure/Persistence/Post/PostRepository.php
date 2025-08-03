<?php

declare(strict_types=1);

namespace App\Blog\Infrastructure\Persistence\Post;

use App\Blog\Domain\Port\PostRepositoryInterface;
use App\Blog\Domain\Post;
use Cycle\Database\DatabaseInterface;
use Cycle\Database\Driver\DriverInterface;
use Cycle\Database\Driver\SQLite\SQLiteDriver;
use Cycle\Database\Injection\Fragment;
use Cycle\Database\Injection\FragmentInterface;
use Cycle\ORM\EntityManagerInterface;
use Cycle\ORM\Select;
use DateTimeImmutable;
use Override;
use Yiisoft\Data\Cycle\Reader\EntityReader;
use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Data\Reader\Sort;

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

    #[Override]
    public function getFullArchive(?int $limit = null): DataReaderInterface
    {
        $sort = Sort::only(['year', 'month', 'count'])->withOrder(['year' => 'desc', 'month' => 'desc']);

        $query = $this
            ->select()
            ->buildQuery()
            ->columns([
                'count(id) count',
                $this->extractFromDateColumn('month'),
                $this->extractFromDateColumn('year'),
            ])
            ->groupBy('year, month');

        $dataReader = new EntityReader($query)->withSort($sort);

        if ($limit !== null) {
            return $dataReader->withLimit($limit);
        }

        return $dataReader;
    }

    #[Override]
    public function getMonthlyArchive(int $year, int $month): DataReaderInterface
    {
        $begin = new DateTimeImmutable()->setDate($year, $month, 1)->setTime(0, 0, 0);
        $end = $begin->setDate($year, $month + 1, 1)->setTime(0, 0, -1);

        $query = $this
            ->select()
            ->andWhere('published_at', 'between', $begin, $end)
            ->load(['tags']);

        return $this->prepareDataReader($query);
    }

    #[Override]
    public function getYearlyArchive(int $year): DataReaderInterface
    {
        $begin = new DateTimeImmutable()->setDate($year, 1, 1)->setTime(0, 0, 0);
        $end = $begin->setDate($year + 1, 1, 1)->setTime(0, 0, -1);

        $query = $this
            ->select()
            ->andWhere('published_at', 'between', $begin, $end)
            ->load('tags')
            ->orderBy(['published_at' => 'asc']);

        return $this->prepareDataReader($query);
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
            if ($entity instanceof Post) {
                $this->entityManager->persist($entity);
            }
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

        foreach ($posts as $entity) {
            if ($entity instanceof Post) {
                $this->entityManager->delete($entity);
            }
        }
        $this->entityManager->run();
    }

    private function prepareDataReader(Select $query): DataReaderInterface
    {
        return new EntityReader($query)
            ->withSort(
                Sort::only(['published_at'])
                    ->withOrder(['published_at' => 'desc']),
            );
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
            return new Fragment("strftime('{$str}', published_at) {$wrappedField}");
        }

        return new Fragment("extract({$attr} from published_at) {$wrappedField}");
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
