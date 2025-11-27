<?php

declare(strict_types=1);

namespace App\Tests\Unit\Blog\Slice\Tag\Service\QueryService;

use App\Blog\Domain\Port\TagRepositoryInterface;
use App\Blog\Domain\Tag;
use App\Blog\Slice\Tag\Service\QueryService\TagQueryService;
use App\Tests\UnitTester;
use Codeception\Test\Unit;
use Cycle\Database\Driver\DriverInterface;
use Cycle\ORM\Select;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use Yiisoft\Data\Cycle\Reader\EntityReader;
use Yiisoft\Data\Reader\Sort;

#[CoversClass(TagQueryService::class)]
final class TagQueryServiceTest extends Unit
{
    protected UnitTester $tester;

    private TagQueryService $service;

    private TagRepositoryInterface&MockObject $tagRepository;

    private Select&MockObject $select;

    public function testFindAllPreloaded(): void
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
                                    'label' => 'ASC',
                                    'created_at' => 'DESC',
                                    'id' => 'ASC',
                                ],
                            ],
                            $arguments,
                        );
                    }

                    return $this->select;
                },
            );

        $selectResult = [
            new Tag('tag1'),
            new Tag('tag2'),
        ];

        $this->select
            ->expects($this->once())
            ->method('fetchAll')
            ->willReturn($selectResult);


        $this->tagRepository
            ->expects($this->once())
            ->method('findAllPreloaded')
            ->willReturn(new EntityReader($this->select));

        $service = new TagQueryService($this->tagRepository);
        $dataReader = $service->findAllPreloaded();
        $data = $dataReader->read();

        $this->assertNull($dataReader->getLimit());
        $sort = $dataReader->getSort();
        $this->assertInstanceOf(Sort::class, $sort);
        $this->assertEquals(['created_at' => 'desc'], $sort->getOrder());
        $this->assertEquals(['id' => 'asc', 'label' => 'asc', 'created_at' => 'asc'], $sort->getDefaultOrder());
        $this->assertEquals($selectResult, $data);
    }

    /**
     * @throws Exception
     */
    public function testGetTagMentions(): void
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
                                    'count' => 'DESC',
                                    'label' => 'ASC',
                                ],
                            ],
                            $arguments,
                        );
                    }

                    return $this->select;
                },
            );

        $selectResult = [
            ['count' => 3, 'label' => 'test-tag'],
            ['count' => 1, 'label' => 'test-tag2'],
        ];

        $this->select
            ->expects($this->once())
            ->method('fetchAll')
            ->willReturn($selectResult);

        $this->tagRepository
            ->expects($this->once())
            ->method('getTagMentions')
            ->willReturn(new EntityReader($this->select));

        $service = new TagQueryService($this->tagRepository);
        $dataReader = $service->getTagMentions();
        $data = $dataReader->read();

        $resultSort = $dataReader->getSort();
        $this->assertEquals(['count' => 'desc'], $resultSort->getOrder());
        $this->assertEquals(
            ['count' => 'asc', 'label' => 'asc'],
            $resultSort->getDefaultOrder(),
        );
        $this->assertEquals($selectResult, $data);
    }

    public function testGetTagMentionsWithLimit(): void
    {
        $limit = 10;

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
                                    'count' => 'DESC',
                                    'label' => 'ASC',
                                ],
                            ],
                            $arguments,
                        );
                    }

                    return $this->select;
                },
            );

        $selectResult = [
            ['count' => 3, 'label' => 'test-tag'],
            ['count' => 1, 'label' => 'test-tag2'],
        ];

        $this->select
            ->expects($this->once())
            ->method('fetchAll')
            ->willReturn($selectResult);

        $this->tagRepository
            ->expects($this->once())
            ->method('getTagMentions')
            ->willReturn(new EntityReader($this->select));

        $service = new TagQueryService($this->tagRepository);
        $dataReader = $service->getTagMentions($limit);
        $data = $dataReader->read();

        $resultSort = $dataReader->getSort();
        $this->assertEquals(['count' => 'desc'], $resultSort->getOrder());
        $this->assertEquals(
            ['count' => 'asc', 'label' => 'asc'],
            $resultSort->getDefaultOrder(),
        );
        $this->assertEquals($limit, $dataReader->getLimit());
        $this->assertEquals($selectResult, $data);
    }

    public function testFindByLabel(): void
    {
        $label = 'test-tag';
        $expectedTag = new Tag($label);

        $this->tagRepository
            ->expects($this->once())
            ->method('findByLabel')
            ->with($label)
            ->willReturn($expectedTag);

        $result = $this->service->findByLabel($label);

        $this->assertSame($expectedTag, $result);
    }

    public function testFindByLabelNotFound(): void
    {
        $label = 'test-tag';

        $this->tagRepository
            ->expects($this->once())
            ->method('findByLabel')
            ->with($label)
            ->willReturn(null);

        $result = $this->service->findByLabel($label);

        $this->assertNull($result);
    }

    public function testGetTag(): void
    {
        $tagId = 1;
        $expectedTag = new Tag('test-tag');

        $this->tagRepository
            ->expects($this->once())
            ->method('getTag')
            ->with($tagId)
            ->willReturn($expectedTag);

        $result = $this->service->getTag($tagId);

        $this->assertSame($expectedTag, $result);
    }

    public function testGetTagNotFound(): void
    {
        $tagId = 1;

        $this->tagRepository
            ->expects($this->once())
            ->method('getTag')
            ->with($tagId)
            ->willReturn(null);

        $result = $this->service->getTag($tagId);

        $this->assertNull($result);
    }

    /**
     * @throws Exception
     */
    #[Override]
    protected function _before(): void
    {
        $this->select = $this->createMock(Select::class);
        $this->tagRepository = $this->createMock(TagRepositoryInterface::class);

        $this->service = new TagQueryService($this->tagRepository);
    }
}
