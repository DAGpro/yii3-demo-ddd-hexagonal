<?php

declare(strict_types=1);

namespace App\Tests\Unit\Blog\Application\Service\AppService\QueryService;

use App\Blog\Application\Service\AppService\QueryService\TagQueryService;
use App\Blog\Domain\Port\TagRepositoryInterface;
use App\Blog\Domain\Tag;
use App\Blog\Infrastructure\Persistence\Tag\TagRepository;
use Cycle\Database\Driver\DriverInterface;
use Cycle\Database\Query\SelectQuery;
use Cycle\ORM\EntityManagerInterface;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\Select;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Yiisoft\Data\Reader\DataReaderInterface;

#[CoversClass(TagQueryService::class)]
final class TagQueryServiceTest extends TestCase
{
    private TagQueryService $service;

    private TagRepositoryInterface $tagRepository;

    private Select&MockObject $select;

    public function testFindAllPreloaded(): void
    {
        $this->select
            ->expects($this->once())
            ->method('__call')
            ->willReturnCallback(
                function (mixed $method, mixed $arguments) {
                    if ($method === 'getDriver') {
                        $driverMock = $this
                            ->createMock(DriverInterface::class);
                        $driverMock
                            ->expects($this->once())
                            ->method('getType')
                            ->willReturn('MySQL');
                        return $driverMock;
                    }
                    return $this->select;
                },
            );

        $service = new TagQueryService($this->tagRepository);

        $result = $service->findAllPreloaded();

        $this->assertInstanceOf(DataReaderInterface::class, $result);
    }

    /**
     * @throws Exception
     */
    public function testGetTagMentions(): void
    {
        $limit = 10;

        $selectQuery = $this->createMock(SelectQuery::class);
        $selectQuery
            ->expects($this->exactly(2))
            ->method('groupBy')
            ->willReturnMap([
                ['posts.@.tag_id', $selectQuery],
                ['label', $selectQuery],
            ]);
        $selectQuery
            ->expects($this->once())
            ->method('columns')
            ->with(['label', 'count(*) count'])
            ->willReturn($selectQuery);

        $this->select
            ->expects($this->once())
            ->method('buildQuery')
            ->willReturn($selectQuery);

        $service = new TagQueryService($this->tagRepository);

        $result = $service->getTagMentions($limit);

        $this->assertInstanceOf(DataReaderInterface::class, $result);
    }

    public function testFindByLabel(): void
    {
        $label = 'test-tag';
        $expectedTag = new Tag($label);

        $this->select
            ->expects($this->once())
            ->method('__call')
            ->willReturnCallback(
                function (mixed $method, mixed $arguments) use ($label) {
                    if ($method === 'where') {
                        $this->assertIsArray($arguments);
                        $this->assertArrayHasKey('label', $arguments[0]);
                        $this->assertSame($label, $arguments[0]['label']);
                        return $this->select;
                    }
                    return $this->select;
                },
            );

        $this->select
            ->expects($this->once())
            ->method('fetchOne')
            ->willReturn($expectedTag);

        $result = $this->service->findByLabel($label);

        $this->assertSame($expectedTag, $result);
    }

    public function testGetTag(): void
    {
        $tagId = 1;
        $expectedTag = new Tag('test-tag');

        $this->select
            ->expects($this->once())
            ->method('__call')
            ->willReturnCallback(
                function (mixed $method, mixed $arguments) use ($tagId) {
                    if ($method === 'where') {
                        $this->assertIsArray($arguments);
                        $this->assertArrayHasKey('id', $arguments[0]);
                        $this->assertSame($tagId, $arguments[0]['id']);
                        return $this->select;
                    }
                    return $this->select;
                },
            );

        $this->select
            ->expects($this->once())
            ->method('fetchOne')
            ->willReturn($expectedTag);

        $result = $this->service->getTag($tagId);

        $this->assertSame($expectedTag, $result);
    }

    /**
     * @throws Exception
     */
    #[Override]
    protected function setUp(): void
    {
        $this->tagRepository = new TagRepository(
            $this->select = $this->createMock(Select::class),
            $this->createMock(EntityManagerInterface::class),
            $this->createMock(ORMInterface::class),
        );

        $this->service = new TagQueryService($this->tagRepository);
    }
}
