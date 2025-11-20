<?php

declare(strict_types=1);

namespace App\Tests\Unit\Blog\Application\Service\AppService\QueryService;

use App\Blog\Application\Service\AppService\QueryService\ArchivePostQueryService;
use App\Blog\Domain\Port\PostRepositoryInterface;
use App\Blog\Domain\Post;
use App\Blog\Domain\Tag;
use App\Blog\Domain\User\Author;
use App\Tests\UnitTester;
use Codeception\Test\Unit;
use Cycle\Database\Driver\DriverInterface;
use Cycle\ORM\Select;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use Yiisoft\Data\Cycle\Reader\EntityReader;

#[CoversClass(ArchivePostQueryService::class)]
final class ArchivePostQueryServiceTest extends Unit
{
    protected UnitTester $tester;

    private ArchivePostQueryService $service;

    private PostRepositoryInterface&MockObject $postRepository;

    private Select&MockObject $select;

    public function testGetFullArchiveWithoutLimit(): void
    {
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

                    if ($method === 'orderBy') {
                        $this->assertEquals('orderBy', $method);
                        $this->assertEquals(
                            [
                                [
                                    'year' => 'DESC',
                                    'month' => 'DESC',
                                    'count' => 'ASC',
                                ],
                            ],
                            $arguments,
                        );
                    }

                    return $this->select;
                },
            );

        $selectResult = [
            ['2024', '02', '2'],
            ['2024', '01', '1'],
            ['2023', '12', '3'],
            ['2023', '11', '4'],
            ['2023', '10', '1'],
        ];

        $this->select
            ->expects($this->once())
            ->method('fetchAll')
            ->willReturn($selectResult);

        $this->postRepository
            ->expects($this->once())
            ->method('getFullArchive')
            ->willReturn(new EntityReader($this->select));

        $dataReader = $this->service->getFullArchive();

        $sortDataReader = $dataReader->getSort();
        $this->assertEquals(
            ['year' => 'asc', 'month' => 'asc', 'count' => 'asc'],
            $sortDataReader->getDefaultOrder(),
        );
        $this->assertEquals(['year' => 'desc', 'month' => 'desc'], $sortDataReader->getOrder());
        $this->assertNull($dataReader->getLimit());
        $this->assertEquals($selectResult, $dataReader->read());
    }

    public function testGetFullArchiveWithLimit(): void
    {
        $limit = 5;

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

                    if ($method === 'orderBy') {
                        $this->assertEquals('orderBy', $method);
                        $this->assertEquals(
                            [
                                [
                                    'year' => 'DESC',
                                    'month' => 'DESC',
                                    'count' => 'ASC',
                                ],
                            ],
                            $arguments,
                        );
                    }

                    return $this->select;
                },
            );

        $selectResult = [
            ['2024', '02', '2'],
            ['2024', '01', '1'],
            ['2023', '12', '3'],
            ['2023', '11', '4'],
            ['2023', '10', '1'],
        ];

        $this->select
            ->expects($this->once())
            ->method('fetchAll')
            ->willReturn($selectResult);

        $this->postRepository
            ->expects($this->once())
            ->method('getFullArchive')
            ->willReturn(new EntityReader($this->select));

        $dataReader = $this->service->getFullArchive($limit);

        $sortDataReader = $dataReader->getSort();
        $this->assertEquals(
            ['year' => 'asc', 'month' => 'asc', 'count' => 'asc'],
            $sortDataReader->getDefaultOrder(),
        );
        $this->assertEquals(['year' => 'desc', 'month' => 'desc'], $sortDataReader->getOrder());
        $this->assertSame(5, $dataReader->getLimit());
        $this->assertEquals($selectResult, $dataReader->read());
    }

    public function testGetMonthlyArchive(): void
    {
        $year = 2023;
        $month = 5;

        $this->select
            ->expects($this->exactly(2))
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
                    return $this->select;
                },
            );

        $tag = new Tag('tag');
        $tag2 = new Tag('tag2');
        $post = new Post('title', 'content', new Author(1, 'Author'));
        $post->addTag($tag);
        $post2 = new Post('title 2', 'content 2', new Author(1, 'Author'));
        $post2->addTag($tag2);
        $selectResult = [
            $post,
            $post2,
        ];

        $this->select
            ->expects($this->once())
            ->method('fetchAll')
            ->willReturn($selectResult);

        $this->postRepository
            ->expects($this->once())
            ->method('getMonthlyArchive')
            ->with($year, $month)
            ->willReturn(new EntityReader($this->select));

        $dataReader = $this->service->getMonthlyArchive($year, $month);

        $this->assertNull($dataReader->getSort());
        $this->assertEquals($selectResult, $dataReader->read());
    }

    public function testGetYearlyArchive(): void
    {
        $year = 2023;

        $this->select
            ->expects($this->exactly(2))
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
                    return $this->select;
                },
            );

        $tag = new Tag('tag');
        $tag2 = new Tag('tag2');
        $post = new Post('title', 'content', new Author(1, 'Author'));
        $post->addTag($tag);
        $post2 = new Post('title 2', 'content 2', new Author(1, 'Author'));
        $post2->addTag($tag2);
        $selectResult = [
            $post,
            $post2,
        ];

        $this->select
            ->expects($this->once())
            ->method('fetchAll')
            ->willReturn($selectResult);

        $this->postRepository
            ->expects($this->once())
            ->method('getYearlyArchive')
            ->with($year)
            ->willReturn(new EntityReader($this->select));

        $dataReader = $this->service->getYearlyArchive($year);

        $this->assertNull($dataReader->getSort());
        $this->assertEquals($selectResult, $dataReader->read());
    }

    /**
     * @throws Exception
     */
    #[Override]
    protected function _before(): void
    {
        $this->select = $this->createMock(Select::class);
        $this->postRepository = $this->createMock(PostRepositoryInterface::class);
        $this->service = new ArchivePostQueryService($this->postRepository);
    }
}
