<?php

declare(strict_types=1);

namespace App\Tests\Unit\Blog\Infrastructure\Persistence\Tag;

use App\Blog\Domain\Tag;
use App\Blog\Infrastructure\Persistence\Tag\TagRepository;
use Cycle\Database\Query\SelectQuery;
use Cycle\ORM\EntityManagerInterface;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\Select;
use Override;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TagRepositoryTest extends TestCase
{
    private TagRepository $repository;
    private Select&MockObject $select;
    private EntityManagerInterface&MockObject $entityManager;
    private ORMInterface&MockObject $orm;

    public function testGetOrCreateWithNewTag(): void
    {
        $label = 'test-tag';

        // Настройка моков
        $this->select
            ->expects($this->once())
            ->method('__call')
            ->with('where', [['label' => $label]])
            ->willReturn($this->select);

        $this->select
            ->expects($this->once())
            ->method('fetchOne')
            ->willReturn(null);

        // Вызов тестируемого метода
        $tag = $this->repository->getOrCreate($label);

        // Проверки
        $this->assertInstanceOf(Tag::class, $tag);
        $this->assertSame($label, $tag->getLabel());
    }

    public function testGetOrCreateWithExistingTag(): void
    {
        $label = 'existing-tag';
        $existingTag = new Tag($label);

        // Настройка моков
        $this->select
            ->expects($this->once())
            ->method('__call')
            ->with('where', [['label' => $label]])
            ->willReturn($this->select);

        $this->select
            ->expects($this->once())
            ->method('fetchOne')
            ->willReturn($existingTag);

        // Вызов тестируемого метода
        $tag = $this->repository->getOrCreate($label);

        // Проверки
        $this->assertSame($existingTag, $tag);
    }

    public function testFindByLabel(): void
    {
        $label = 'test-find';
        $expectedTag = new Tag($label);

        // Настройка моков
        $this->select
            ->expects($this->once())
            ->method('__call')
            ->with('where', [['label' => $label]])
            ->willReturn($this->select);

        $this->select
            ->expects($this->once())
            ->method('fetchOne')
            ->willReturn($expectedTag);

        // Вызов тестируемого метода
        $tag = $this->repository->findByLabel($label);

        // Проверки
        $this->assertSame($expectedTag, $tag);
    }

    public function testFindByLabelNotFound(): void
    {
        $label = 'non-existent';

        // Настройка моков
        $this->select
            ->expects($this->once())
            ->method('__call')
            ->with('where', [['label' => $label]])
            ->willReturn($this->select);

        $this->select
            ->expects($this->once())
            ->method('fetchOne')
            ->willReturn(null);

        // Вызов тестируемого метода
        $tag = $this->repository->findByLabel($label);

        // Проверки
        $this->assertNull($tag);
    }

    public function testGetTag(): void
    {
        $tagId = 1;
        $expectedTag = new Tag('test-tag');

        // Настройка моков
        $this->select
            ->expects($this->once())
            ->method('__call')
            ->with('where', [['id' => $tagId]])
            ->willReturn($this->select);

        $this->select
            ->expects($this->once())
            ->method('fetchOne')
            ->willReturn($expectedTag);

        // Вызов тестируемого метода
        $tag = $this->repository->getTag($tagId);

        // Проверки
        $this->assertSame($expectedTag, $tag);
    }

    public function testSave(): void
    {
        $tag = new Tag('test-save');
        $tags = [$tag];

        // Настройка моков
        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($tag);

        $this->entityManager
            ->expects($this->once())
            ->method('run');

        // Вызов тестируемого метода
        $this->repository->save($tags);
    }

    public function testSaveEmptyArray(): void
    {
        // Настройка моков - не должно быть вызовов persist и run
        $this->entityManager
            ->expects($this->never())
            ->method('persist');

        $this->entityManager
            ->expects($this->never())
            ->method('run');

        // Вызов тестируемого метода с пустым массивом
        $this->repository->save([]);
    }

    public function testDelete(): void
    {
        $tag = new Tag('test-delete');
        $tags = [$tag];

        // Настройка моков
        $this->entityManager
            ->expects($this->once())
            ->method('delete')
            ->with($tag);

        $this->entityManager
            ->expects($this->once())
            ->method('run');

        // Вызов тестируемого метода
        $this->repository->delete($tags);
    }

    public function testDeleteEmptyArray(): void
    {
        // Настройка моков - не должно быть вызовов delete и run
        $this->entityManager
            ->expects($this->never())
            ->method('delete');

        $this->entityManager
            ->expects($this->never())
            ->method('run');

        // Вызов тестируемого метода с пустым массивом
        $this->repository->delete([]);
    }

    /**
     * @throws Exception
     */
    public function testGetTagMentions(): void
    {
        $limit = 5;

        $selectQuery = $this->createMock(SelectQuery::class);

        $this->select
            ->expects($this->once())
            ->method('buildQuery')
            ->willReturn($selectQuery);

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

        $result = $this->repository->getTagMentions($limit);

        $this->assertInstanceOf(SelectQuery::class, $result);
    }

    /**
     * @throws Exception
     */
    #[Override]
    protected function setUp(): void
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
