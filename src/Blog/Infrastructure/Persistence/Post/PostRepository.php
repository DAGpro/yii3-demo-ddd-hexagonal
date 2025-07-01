<?php

declare(strict_types=1);

namespace App\Blog\Infrastructure\Persistence\Post;

use App\Blog\Domain\Port\PostRepositoryInterface;
use Cycle\Database\DatabaseInterface;
use Cycle\Database\Driver\DriverInterface;
use Cycle\Database\Driver\SQLite\SQLiteDriver;
use Cycle\Database\Injection\Fragment;
use Cycle\Database\Injection\FragmentInterface;
use Cycle\ORM\EntityManagerInterface;
use Cycle\ORM\Select;

final class PostRepository extends Select\Repository implements PostRepositoryInterface
{
    public function __construct(
        protected Select $select,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct($select);
    }

    #[\Override]
    public function save(array $posts): void
    {
        foreach ($posts as $entity) {
            $this->entityManager->persist($entity);
        }
        $this->entityManager->run();
    }

    #[\Override]
    public function delete(array $posts): void
    {
        foreach ($posts as $entity) {
            $this->entityManager->delete($entity);
        }
        $this->entityManager->run();
    }

    /**
     * @param string $attr Can be 'day', 'month' or 'year'
     *
     * @return FragmentInterface
     */
    public function extractFromDateColumn(string $attr): FragmentInterface
    {
        $driver = $this->getDriver();
        $wrappedField = $driver->getQueryCompiler()->quoteIdentifier($attr);
        if ($driver instanceof SQLiteDriver) {
            $str = ['year' => '%Y', 'month' => '%m', 'day' => '%d'][$attr];
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
            ->getSource()
            ->getDatabase()
            ->getDriver(DatabaseInterface::READ);
    }
}
