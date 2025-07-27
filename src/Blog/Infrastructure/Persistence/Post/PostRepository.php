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
use Override;

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
    /**
     * @param iterable<Post> $posts
     * @psalm-assert-if-true !null $posts
     */
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

    #[Override]
    /**
     * @param iterable<Post> $posts
     * @psalm-assert-if-true !null $posts
     */
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

    /**
     * @param 'day'|'month'|'year' $attr
     * @return FragmentInterface
     */
    #[Override]
    public function extractFromDateColumn(string $attr): FragmentInterface
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

    /**
     * @psalm-suppress UndefinedInterfaceMethod
     */
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
