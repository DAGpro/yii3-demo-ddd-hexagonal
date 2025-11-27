<?php

declare(strict_types=1);

namespace App\Tests\Unit\Blog\Slice\Post\Repository;

use App\Blog\Domain\Post;
use App\Blog\Domain\Tag;
use App\Blog\Domain\User\Author;
use App\Blog\Slice\Post\Repository\PostRepository;
use App\Tests\UnitTester;
use Codeception\Test\Unit;
use Cycle\Database\DatabaseInterface;
use Cycle\Database\Driver\CompilerInterface;
use Cycle\Database\Driver\DriverInterface;
use Cycle\Database\Driver\MySQL\MySQLDriver;
use Cycle\Database\Driver\SQLite\SQLiteDriver;
use Cycle\Database\Query\SelectQuery;
use Cycle\ORM\EntityManagerInterface;
use Cycle\ORM\FactoryInterface;
use Cycle\ORM\SchemaInterface;
use Cycle\ORM\Select;
use Cycle\ORM\Select\QueryBuilder;
use Cycle\ORM\Select\SourceInterface;
use Cycle\ORM\Service\SourceProviderInterface;
use DateMalformedStringException;
use DateTimeImmutable;
use Override;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use Yiisoft\Data\Reader\DataReaderInterface;

final class PostRepositoryTest extends Unit
{
    protected UnitTester $tester;

    private Select&MockObject $select;

    private SelectQuery&MockObject $selectQuery;

    private EntityManagerInterface&MockObject $entityManager;

    private PostRepository $repository;

    /**
     * @throws Exception
     */
    public function testGetFullArchive(): void
    {
        $driverMock = $this
            ->createMock(MySQLDriver::class);
        $driverMock
            ->expects($this->once())
            ->method('getType')
            ->willReturn('MySQL');

        $this->getBuilder($driverMock);
        $this->select
            ->expects($this->once())
            ->method('buildQuery')
            ->willReturn($this->selectQuery);

        $this->selectQuery
            ->expects($this->once())
            ->method('getDriver')
            ->willReturn($driverMock);

        $this->selectQuery
            ->expects($this->once())
            ->method('columns')
            ->with(
                $this->callback(
                    function ($columns) {
                        $this->assertSame('count(id) count', $columns[0]);
                        $this->assertStringStartsWith('extract(month from', $columns[1]->__toString());
                        $this->assertStringStartsWith('extract(year from', $columns[2]->__toString());
                        return true;
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

    /**
     * @throws Exception
     */
    public function testGetFullArchiveIsDriverSQLite(): void
    {
        $driverMock = $this
            ->createMock(SQLiteDriver::class);
        $driverMock
            ->expects($this->once())
            ->method('getType')
            ->willReturn('SQLite');

        $this->getBuilder($driverMock);

        $this->select
            ->expects($this->once())
            ->method('buildQuery')
            ->willReturn($this->selectQuery);

        $this->selectQuery
            ->expects($this->once())
            ->method('getDriver')
            ->willReturn($driverMock);

        $this->selectQuery
            ->expects($this->once())
            ->method('columns')
            ->with(
                $this->callback(
                    function ($columns) {
                        $this->assertSame('count(id) count', $columns[0]);
                        $this->assertStringStartsWith('strftime(\'%m', $columns[1]->__toString());
                        $this->assertStringStartsWith('strftime(\'%Y', $columns[2]->__toString());
                        return true;
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
                function (string $method, array $arguments) use ($query) {
                    if ($method === 'getDriver') {
                        $driverMock = $this
                            ->createMock(DriverInterface::class);
                        $driverMock
                            ->expects($this->once())
                            ->method('getType')
                            ->willReturn('SQLite');
                        return $driverMock;
                    }
                    if ($method === 'andWhere') {
                        $this->assertSame('published_at', $arguments[0]);
                        $this->assertSame('between', $arguments[1]);
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
                function (string $method, array $arguments) {
                    if ($method === 'getDriver') {
                        $driverMock = $this
                            ->createMock(DriverInterface::class);
                        $driverMock
                            ->expects($this->once())
                            ->method('getType')
                            ->willReturn('SQLite');
                        return $driverMock;
                    }
                    if ($method === 'andWhere') {
                        $this->assertSame('published_at', $arguments[0]);
                        $this->assertSame('between', $arguments[1]);
                        return $this->select;
                    }
                    if ($method === 'orderBy') {
                        $this->assertArrayHasKey('published_at', $arguments[0]);
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
            ->willReturnCallback(
                function ($entity) use (&$persistedEntities) {
                    $persistedEntities[] = $entity;
                    return $this->entityManager;
                },
            );

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
            ->willReturnCallback(
                function ($entity) use (&$deletedEntities) {
                    $deletedEntities[] = $entity;
                    return $this->entityManager;
                },
            );

        $this->entityManager
            ->expects($this->once())
            ->method('run');

        $this->repository->delete([$post1, $post2]);

        $this->assertCount(2, $deletedEntities);
        $this->assertContains($post1, $deletedEntities);
        $this->assertContains($post2, $deletedEntities);
    }

    public function testFindAllWithTags(): void
    {
        $this->select
            ->expects($this->once())
            ->method('load')
            ->with(['tags'])
            ->willReturn($this->select);

        $result = $this->repository->getAllWithPreloadedTags();

        $this->assertInstanceOf(DataReaderInterface::class, $result);
    }

    public function testFindByTag(): void
    {
        $tag = $this->createMock(Tag::class);
        $tag->method('getLabel')->willReturn('test-tag');

        $this->select
            ->expects($this->once())
            ->method('load')
            ->with(['tags'])
            ->willReturn($this->select);

        $this->select
            ->expects($this->exactly(2))
            ->method('__call')
            ->willReturnCallback(
                function (string $method, array $args) {
                    if ($method === 'getDriver') {
                        $driverMock = $this
                            ->createMock(DriverInterface::class);
                        $driverMock
                            ->expects($this->once())
                            ->method('getType')
                            ->willReturn('MySQL');
                        return $driverMock;
                    }
                    $this->assertEquals('where', $method);
                    $this->assertEquals(['tags.label' => 'test-tag'], $args[0]);
                    return $this->select;
                },
            );

        $result = $this->repository->findByTagWithPreloadedTags($tag);

        $this->assertInstanceOf(DataReaderInterface::class, $result);
    }

    public function testFindBySlug(): void
    {
        $slug = 'test-post';
        $postMock = $this->createMock(Post::class);

        $this->select
            ->expects($this->once())
            ->method('__call')
            ->willReturnCallback(
                function (string $method, array $args) use ($slug) {
                    $this->assertEquals('where', $method);
                    $this->assertEquals(['slug' => $slug], $args[0]);
                    return $this->select;
                },
            );

        $this->select
            ->expects($this->once())
            ->method('fetchOne')
            ->willReturn($postMock);

        $result = $this->repository->findBySlug($slug);

        $this->assertSame($postMock, $result);
    }

    public function testFindById(): void
    {
        $id = 1;
        $postMock = $this->createMock(Post::class);

        $this->select
            ->expects($this->once())
            ->method('__call')
            ->willReturnCallback(
                function (string $method, array $args) use ($id) {
                    $this->assertEquals('where', $method);
                    $this->assertEquals(['id' => $id], $args[0]);
                    return $this->select;
                },
            );

        $this->select
            ->expects($this->once())
            ->method('fetchOne')
            ->willReturn($postMock);

        $result = $this->repository->findById($id);

        $this->assertSame($postMock, $result);
    }

    public function testFindFullPostBySlugWithPreloadedTagsAndComments(): void
    {
        $slug = 'test-post';
        $postMock = $this->createMock(Post::class);

        $this->select
            ->expects($this->once())
            ->method('__call')
            ->willReturnCallback(
                function (string $method, array $args) use ($slug) {
                    $this->assertEquals('where', $method);
                    $this->assertEquals(['slug' => $slug], $args[0]);
                    return $this->select;
                },
            );

        $this->select
            ->expects($this->exactly(2))
            ->method('load')
            ->willReturnCallback(
                function (string|array $relation, ?array $options = null) {
                    if ($relation === 'comments') {
                        $this->assertEquals('comments', $relation);
                        $this->assertEquals([
                            'method' => Select::OUTER_QUERY,
                        ], $options);
                        return $this->select;
                    }

                    if (is_array($relation) && $relation[0] === 'tags') {
                        $this->assertEquals('tags', $relation[0]);
                        return $this->select;
                    }

                    $this->fail('Fail load() call');
                },
            );

        $this->select
            ->expects($this->once())
            ->method('fetchOne')
            ->willReturn($postMock);

        $result = $this->repository->fullPostBySlug($slug);

        $this->assertSame($postMock, $result);
    }

    public function testFindAllForModerationWithPreloadedTags(): void
    {
        $this->select
            ->expects($this->once())
            ->method('scope')
            ->willReturn($this->select);

        $this->select
            ->expects($this->exactly(2))
            ->method('__call')
            ->willReturnCallback(
                function (string $method, array $args) {
                    if ($method === 'getDriver') {
                        $driverMock = $this
                            ->createMock(DriverInterface::class);
                        $driverMock
                            ->expects($this->once())
                            ->method('getType')
                            ->willReturn('MySQL');
                        return $driverMock;
                    }
                    $this->assertEquals('andWhere', $method);
                    $this->assertEquals(['deleted_at', '=', null], $args);
                    return $this->select;
                },
            );

        $this->select
            ->expects($this->once())
            ->method('load')
            ->with(['tags'])
            ->willReturn($this->select);

        $result = $this->repository->getAllForModerationWithPreloadedTags();

        $this->assertInstanceOf(DataReaderInterface::class, $result);
    }

    public function testFindForModeration(): void
    {
        $id = 1;
        $postMock = $this->createMock(Post::class);

        $this->select
            ->expects($this->once())
            ->method('scope')
            ->willReturn($this->select);

        $this->select
            ->expects($this->exactly(2))
            ->method('__call')
            ->willReturnCallback(
                function (string $method, array $args) use ($id) {
                    if ($method === 'getDriver') {
                        $driverMock = $this
                            ->createMock(DriverInterface::class);
                        $driverMock
                            ->expects($this->once())
                            ->method('getType')
                            ->willReturn('SQLite');
                        return $driverMock;
                    }
                    if ($method === 'where') {
                        $this->assertEquals('where', $method);
                        $this->assertEquals(['id', '=', $id], $args);
                        return $this->select;
                    }
                    $this->assertEquals('andWhere', $method);
                    $this->assertEquals(['deleted_at', '=', null], $args);
                    return $this->select;
                },
            );

        $this->select
            ->expects($this->once())
            ->method('fetchOne')
            ->willReturn($postMock);

        $result = $this->repository->findByIdForModeration($id);

        $this->assertSame($postMock, $result);
    }

    public function testFindAuthorPostsWithPreloadedTags(): void
    {
        $author = $this->createMock(Author::class);
        $author->method('getId')->willReturn(1);

        $this->select
            ->expects($this->once())
            ->method('scope')
            ->willReturn($this->select);

        $this->select
            ->expects($this->once())
            ->method('load')
            ->with(['tags'])
            ->willReturn($this->select);

        $this->select
            ->expects($this->exactly(3))
            ->method('__call')
            ->willReturnCallback(
                function (string $method, array $args) {
                    if ($method === 'getDriver') {
                        $driverMock = $this
                            ->createMock(DriverInterface::class);
                        $driverMock
                            ->expects($this->once())
                            ->method('getType')
                            ->willReturn('SQLite');
                        return $driverMock;
                    }
                    if ($args[0] === 'author_id') {//is_array($args[0]) && array_key_exists('author_id', $args[0])
                        $this->assertEquals('where', $method);
                        $this->assertEquals(1, $args[1]);
                        return $this->select;
                    }
                    if ($args[0] === 'deleted_at') {
                        $this->assertEquals('andWhere', $method);
                        $this->assertEquals(null, $args[1]);
                        return $this->select;
                    }
                    $this->fail('Unexpected __call call');
                },
            );

        $result = $this->repository->findByAuthorNotDeletedPostWithPreloadedTags($author);

        $this->assertInstanceOf(DataReaderInterface::class, $result);
    }

    public function testFindPostBySlugWithPreloadedTags(): void
    {
        $slug = 'test-post';
        $postMock = $this->createMock(Post::class);

        $this->select
            ->expects($this->once())
            ->method('scope')
            ->willReturn($this->select);

        $this->select
            ->expects($this->once())
            ->method('load')
            ->with(['tags'])
            ->willReturn($this->select);

        $this->select
            ->expects($this->exactly(2))
            ->method('__call')
            ->willReturnCallback(
                function (string $method, array $args) use ($slug) {
                    if ($method === 'getDriver') {
                        $driverMock = $this
                            ->createMock(DriverInterface::class);
                        $driverMock
                            ->expects($this->once())
                            ->method('getType')
                            ->willReturn('MySQL');
                        return $driverMock;
                    }
                    if ($args[0] === 'slug') {
                        $this->assertEquals('andWhere', $method);
                        $this->assertEquals(['slug', '=', $slug], $args);
                        return $this->select;
                    }
                    if ($args[0] === 'deleted_at') {
                        $this->assertEquals('andWhere', $method);
                        $this->assertEquals(['deleted_at', '=', null], $args);
                        return $this->select;
                    }
                    $this->fail('Unexpected __call call');
                },
            );

        $this->select
            ->expects($this->once())
            ->method('fetchOne')
            ->willReturn($postMock);

        $result = $this->repository->findBySlugNotDeletedPostWithPreloadedTags($slug);

        $this->assertSame($postMock, $result);
    }

    /**
     * @throws DateMalformedStringException
     */
    public function testGetMaxUpdatedAt(): void
    {
        $expectedTime = '2023-01-01 12:00:00';

        $this->select
            ->expects($this->once())
            ->method('__call')
            ->willReturnCallback(
                function (string $method, array $args) use ($expectedTime) {
                    $this->assertEquals('max', $method);
                    $this->assertEquals(['updated_at'], $args);
                    return $expectedTime;
                },
            )
            ->willReturn($expectedTime);

        $result = $this->repository->getMaxUpdatedAt();

        $this->assertEquals(new DateTimeImmutable($expectedTime), $result);
    }

    public function getBuilder(DriverInterface&MockObject $driver): void
    {
        $source = $this->createMock(SourceInterface::class);
        $database = $this->createMock(DatabaseInterface::class);
        $compiler = $this->createMock(CompilerInterface::class);

        $schema = $this->createMock(SchemaInterface::class);
        $schema
            ->expects($this->any())
            ->method('define')
            ->willReturn([]);

        $sourceProvider = $this->createMock(SourceProviderInterface::class);
        $sourceProvider->method('getSource')->willReturn($source);
        $source->method('getDatabase')->willReturn($database);
        $database->method('getDriver')->willReturn($driver);
        $driver->method('getQueryCompiler')->willReturn($compiler);
        $compiler->method('quoteIdentifier')->willReturn('`quoteIdentifier`');

        $queryBuilder = new QueryBuilder(
            $this->createMock(SelectQuery::class),
            new Select\RootLoader(
                $schema,
                $sourceProvider,
                $this->createMock(FactoryInterface::class),
                'target',
                false,
            ),
        );

        $this->select->method('getBuilder')->willReturn($queryBuilder);
    }

    /**
     * @throws Exception
     */
    #[Override]
    protected function _before(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->select = $this->createMock(Select::class);
        $this->selectQuery = $this->createMock(SelectQuery::class);
        $this->repository = new PostRepository($this->select, $this->entityManager);
    }
}
