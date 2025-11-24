<?php

declare(strict_types=1);

namespace App\Tests\Unit\Blog\Infrastructure\Persistence\Tag;

use App\Blog\Domain\Tag;
use App\Blog\Infrastructure\Persistence\Tag\TagRepository;
use App\Tests\UnitTester;
use Codeception\Test\Unit;
use Cycle\Database\Driver\DriverInterface;
use Cycle\Database\Query\SelectQuery;
use Cycle\ORM\EntityManagerInterface;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\Select;
use Override;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use Yiisoft\Data\Reader\DataReaderInterface;

class TagRepositoryTest extends Unit
{
    protected UnitTester $tester;

    private TagRepository $repository;
    private Select&MockObject $select;
    private EntityManagerInterface&MockObject $entityManager;
    private ORMInterface&MockObject $orm;

    public function testFindAllPreloaded()
    {
        $this->select
            ->expects($this->once())
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

        $dataReader = $this->repository->findAllPreloaded();

        $this->assertInstanceOf(DataReaderInterface::class, $dataReader);
    }

    public function testGetOrCreateWithNewTag(): void
    {
        $label = 'test-tag';

        $this->select
            ->expects($this->once())
            ->method('__call')
            ->with('where', [['label' => $label]])
            ->willReturn($this->select);

        $this->select
            ->expects($this->once())
            ->method('fetchOne')
            ->willReturn(null);

        $tag = $this->repository->getOrCreate($label);

        $this->assertSame($label, $tag->getLabel());
    }

    public function testGetOrCreateWithExistingTag(): void
    {
        $label = 'existing-tag';
        $existingTag = new Tag($label);

        $this->select
            ->expects($this->once())
            ->method('__call')
            ->with('where', [['label' => $label]])
            ->willReturn($this->select);

        $this->select
            ->expects($this->once())
            ->method('fetchOne')
            ->willReturn($existingTag);

        $tag = $this->repository->getOrCreate($label);

        $this->assertSame($existingTag, $tag);
    }

    public function testFindByLabel(): void
    {
        $label = 'test-find';
        $expectedTag = new Tag($label);

        $this->select
            ->expects($this->once())
            ->method('__call')
            ->with('where', [['label' => $label]])
            ->willReturn($this->select);

        $this->select
            ->expects($this->once())
            ->method('fetchOne')
            ->willReturn($expectedTag);

        $tag = $this->repository->findByLabel($label);

        $this->assertSame($expectedTag, $tag);
    }

    public function testFindByLabelNotFound(): void
    {
        $label = 'non-existent';

        $this->select
            ->expects($this->once())
            ->method('__call')
            ->with('where', [['label' => $label]])
            ->willReturn($this->select);

        $this->select
            ->expects($this->once())
            ->method('fetchOne')
            ->willReturn(null);

        $tag = $this->repository->findByLabel($label);

        $this->assertNull($tag);
    }

    public function testGetTag(): void
    {
        $tagId = 1;
        $expectedTag = new Tag('test-tag');

        $this->select
            ->expects($this->once())
            ->method('__call')
            ->with('where', [['id' => $tagId]])
            ->willReturn($this->select);

        $this->select
            ->expects($this->once())
            ->method('fetchOne')
            ->willReturn($expectedTag);

        $tag = $this->repository->getTag($tagId);

        $this->assertSame($expectedTag, $tag);
    }

    public function testSave(): void
    {
        $tag = new Tag('test-save');
        $tags = [$tag];

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($tag);

        $this->entityManager
            ->expects($this->once())
            ->method('run');

        $this->repository->save($tags);
    }

    public function testSaveEmptyArray(): void
    {
        $this->entityManager
            ->expects($this->never())
            ->method('persist');

        $this->entityManager
            ->expects($this->never())
            ->method('run');

        $this->repository->save([]);
    }

    public function testDelete(): void
    {
        $tag = new Tag('test-delete');
        $tags = [$tag];

        $this->entityManager
            ->expects($this->once())
            ->method('delete')
            ->with($tag);

        $this->entityManager
            ->expects($this->once())
            ->method('run');

        $this->repository->delete($tags);
    }

    public function testDeleteEmptyArray(): void
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
    public function testGetTagMentions(): void
    {
        $selectQuery = $this->createMock(SelectQuery::class);

        $this->select
            ->expects($this->once())
            ->method('with')
            ->willReturn($this->select);

        $this->select
            ->expects($this->once())
            ->method('buildQuery')
            ->willReturn($selectQuery);

        $selectQuery
            ->expects($this->once())
            ->method('columns')
            ->with(['label', 'count(*) count'])
            ->willReturn($selectQuery);

        $selectQuery
            ->expects($this->once())
            ->method('groupBy')
            ->with('tag.label, tag_id')
            ->willReturn($selectQuery);

        $result = $this->repository->getTagMentions();

        $this->assertInstanceOf(DataReaderInterface::class, $result);
    }

    /**
     * @throws Exception
     */
    #[Override]
    protected function _before(): void
    {
        $this->select = $this->createMock(Select::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->orm = $this->createMock(ORMInterface::class);

        $this->repository = new TagRepository(
            $this->select,
            $this->entityManager,
            $this->orm,
        );
    }
}
