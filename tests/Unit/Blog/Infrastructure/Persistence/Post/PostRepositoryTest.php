<?php

declare(strict_types=1);

namespace App\Tests\Unit\Blog\Infrastructure\Persistence\Post;

use App\Blog\Domain\Post;
use App\Blog\Infrastructure\Persistence\Post\PostRepository;
use Cycle\Database\DatabaseInterface;
use Cycle\Database\Driver\CompilerInterface;
use Cycle\Database\Driver\DriverInterface;
use Cycle\Database\Driver\SQLite\SQLiteDriver;
use Cycle\Database\Query\SelectQuery;
use Cycle\ORM\EntityManagerInterface;
use Cycle\ORM\FactoryInterface;
use Cycle\ORM\SchemaInterface;
use Cycle\ORM\Select;
use Cycle\ORM\Select\LoaderInterface;
use Cycle\ORM\Select\QueryBuilder;
use Cycle\ORM\Select\SourceInterface;
use Cycle\ORM\Service\SourceProviderInterface;
use Override;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Yiisoft\Data\Reader\DataReaderInterface;

final class PostRepositoryTest extends TestCase
{
    private Select&MockObject $select;

    private SelectQuery&MockObject $selectQuery;

    private EntityManagerInterface&MockObject $entityManager;

    private PostRepository $repository;

    private QueryBuilder $queryBuilder;

    private SourceInterface&MockObject $source;

    private LoaderInterface&MockObject $loader;

    private DatabaseInterface&MockObject $database;

    private DriverInterface&MockObject $driver;

    private CompilerInterface&MockObject $compiler;

    /**
     * @throws Exception
     */
    public function testGetFullArchive(): void
    {
        $this->select
            ->expects($this->once())
            ->method('buildQuery')
            ->willReturn($this->selectQuery);

        $this->selectQuery
            ->expects($this->once())
            ->method('columns')
            ->with(
                $this->callback(
                    function ($columns) {
                        return is_array($columns) &&
                            in_array('count(id) count', $columns, true) &&
                            count($columns) === 3;
                    },
                ),
            )
            ->willReturn($this->selectQuery);

        $this->selectQuery
            ->expects($this->once())
            ->method('groupBy')
            ->with('year, month')
            ->willReturn($this->selectQuery);

        $result = $this->repository->getFullArchive();
        $this->assertInstanceOf(DataReaderInterface::class, $result);
    }

    public function testGetMonthlyArchive(): void
    {
        $year = 2023;
        $month = 5;

        $query = $this->select;
        $query
            ->expects($this->exactly(2))
            ->method('__call')
            ->willReturnCallback(
                function ($method, $arguments) use ($query) {
                    if ($method === 'getDriver') {
                        $driverMock = $this
                            ->createMock(DriverInterface::class);
                        $driverMock
                            ->expects($this->once())
                            ->method('getType')
                            ->willReturn('SQLite');
                        return $driverMock;
                    }
                    if ($method === 'andWhere' && $arguments[0] === 'published_at' && $arguments[1] === 'between') {
                        return $query;
                    }

                    return $query;
                },
            );

        $query
            ->expects($this->once())
            ->method('load')
            ->with(['tags'])
            ->willReturn($query);

        $result = $this->repository->getMonthlyArchive($year, $month);
        $this->assertInstanceOf(DataReaderInterface::class, $result);
    }

    public function testGetYearlyArchive(): void
    {
        $year = 2023;

        $this->select
            ->expects($this->exactly(3))
            ->method('__call')
            ->willReturnCallback(
                function ($method, $arguments) {
                    if ($method === 'getDriver') {
                        $driverMock = $this
                            ->createMock(DriverInterface::class);
                        $driverMock
                            ->expects($this->once())
                            ->method('getType')
                            ->willReturn('SQLite');
                        return $driverMock;
                    }
                    if ($method === 'andWhere' && $arguments[0] === 'published_at' && $arguments[1] === 'between') {
                        return $this->select;
                    }
                    if ($method === 'orderBy' && $arguments[0] === ['published_at' => 'asc']) {
                        return $this->select;
                    }
                    return $this->select;
                },
            );

        $this->select
            ->expects($this->once())
            ->method('load')
            ->with('tags')
            ->willReturn($this->select);

        $result = $this->repository->getYearlyArchive($year);
        $this->assertInstanceOf(DataReaderInterface::class, $result);
    }

    public function testSaveWithEmptyArray(): void
    {
        $this->entityManager
            ->expects($this->never())
            ->method('persist');

        $this->entityManager
            ->expects($this->never())
            ->method('run');

        $this->repository->save([]);
    }

    /**
     * @throws Exception
     */
    public function testSaveWithPosts(): void
    {
        $post1 = $this->createMock(Post::class);
        $post2 = $this->createMock(Post::class);

        $persistedEntities = [];

        $this->entityManager
            ->expects($this->exactly(2))
            ->method('persist')
            ->willReturnCallback(function ($entity) use (&$persistedEntities) {
                $persistedEntities[] = $entity;
                return $this->entityManager;
            });

        $this->entityManager
            ->expects($this->once())
            ->method('run');

        $this->repository->save([$post1, $post2]);

        $this->assertCount(2, $persistedEntities);
        $this->assertContains($post1, $persistedEntities);
        $this->assertContains($post2, $persistedEntities);
    }

    public function testDeleteWithEmptyArray(): void
    {
        $this->entityManager
            ->expects($this->never())
            ->method('delete');

        $this->entityManager
            ->expects($this->never())
            ->method('run');

        $this->repository->delete([]);
    }

    /**
     * @throws Exception
     */
    public function testDeleteWithPosts(): void
    {
        $post1 = $this->createMock(Post::class);
        $post2 = $this->createMock(Post::class);

        $deletedEntities = [];

        $this->entityManager
            ->expects($this->exactly(2))
            ->method('delete')
            ->willReturnCallback(function ($entity) use (&$deletedEntities) {
                $deletedEntities[] = $entity;
                return $this->entityManager;
            });

        $this->entityManager
            ->expects($this->once())
            ->method('run');

        $this->repository->delete([$post1, $post2]);

        $this->assertCount(2, $deletedEntities);
        $this->assertContains($post1, $deletedEntities);
        $this->assertContains($post2, $deletedEntities);
    }

    /**
     * @throws Exception
     */
    #[Override]
    protected function setUp(): void
    {
        // Сначала создаем все моки
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $schema = $this->createMock(SchemaInterface::class);
        $schema
            ->expects($this->any())
            ->method('define')
            ->willReturn([]);
        $this->queryBuilder = new QueryBuilder(
            $this->createMock(SelectQuery::class),
            new Select\RootLoader(
                $schema,
                $this->createMock(SourceProviderInterface::class),
                $this->createMock(FactoryInterface::class),
                'target',
                false,
            ),
        );

        $this->loader = $this->createMock(LoaderInterface::class);
        $this->source = $this->createMock(SourceInterface::class);
        $this->database = $this->createMock(DatabaseInterface::class);
        $this->driver = $this->createMock(SQLiteDriver::class);
        $this->compiler = $this->createMock(CompilerInterface::class);

        $this->source->method('getDatabase')->willReturn($this->database);
        $this->database->method('getDriver')->willReturn($this->driver);
        $this->driver->method('getQueryCompiler')->willReturn($this->compiler);
        $this->compiler->method('quoteIdentifier')->willReturn('quoted_identifier');

        $this->select = $this->createMock(Select::class);
        $this->select->method('getBuilder')->willReturn($this->queryBuilder);

        $this->select->method('load')->willReturn($this->select);
        $this->selectQuery = $this->createMock(SelectQuery::class);

        $this->repository = new PostRepository($this->select, $this->entityManager);
    }
}
