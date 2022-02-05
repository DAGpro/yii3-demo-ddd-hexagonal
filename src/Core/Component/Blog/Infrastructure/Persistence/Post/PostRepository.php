<?php

declare(strict_types=1);

namespace App\Core\Component\Blog\Infrastructure\Persistence\Post;

use App\Core\Component\Blog\Domain\Port\PostRepositoryInterface;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\Select;
use Cycle\ORM\Transaction;
use Cycle\ORM\TransactionInterface;
use Spiral\Database\DatabaseInterface;
use Spiral\Database\Driver\DriverInterface;
use Spiral\Database\Driver\SQLite\SQLiteDriver;
use Spiral\Database\Injection\Fragment;
use Spiral\Database\Injection\FragmentInterface;

final class PostRepository extends Select\Repository implements PostRepositoryInterface
{
    private TransactionInterface $transaction;

    public function __construct(Select $select, ORMInterface $orm)
    {
        $this->transaction = new Transaction($orm);
        parent::__construct($select);
    }

    public function save(array $posts): void
    {
        foreach ($posts as $entity) {
            $this->transaction->persist($entity);
        }
        $this->transaction->run();
    }

    public function delete(array $posts): void
    {
        foreach ($posts as $entity) {
            $this->transaction->delete($entity);
        }
        $this->transaction->run();
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
        return $this->select()
            ->getBuilder()
            ->getLoader()
            ->getSource()
            ->getDatabase()
            ->getDriver(DatabaseInterface::READ);
    }
}
