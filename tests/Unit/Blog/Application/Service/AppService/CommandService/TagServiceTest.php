<?php

declare(strict_types=1);

namespace App\Tests\Unit\Blog\Application\Service\AppService\CommandService;

use App\Blog\Application\Service\AppService\CommandService\TagService;
use App\Blog\Domain\Exception\BlogNotFoundException;
use App\Blog\Domain\Port\TagRepositoryInterface;
use App\Blog\Domain\Tag;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(TagService::class)]
final class TagServiceTest extends TestCase
{
    private TagService $service;

    private TagRepositoryInterface&MockObject $tagRepository;

    private int $tagId = 1;
    private string $tagLabel = 'test-tag';
    private string $newTagLabel = 'updated-tag';
    private Tag $tag;

    /**
     * @throws BlogNotFoundException
     */
    public function testChangeTagSuccess(): void
    {
        $this->tagRepository
            ->expects($this->once())
            ->method('getTag')
            ->with($this->tagId)
            ->willReturn($this->tag);

        $this->tagRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function ($tags) {
                $this->assertIsArray($tags);
                $this->assertCount(1, $tags);
                $tag = $tags[0];
                $this->assertEquals($this->newTagLabel, $tag->getLabel());
                return true;
            }),
            );

        $this->service->changeTag($this->tagId, $this->newTagLabel);
    }

    public function testChangeTagNotFound(): void
    {
        $this->tagRepository
            ->expects($this->once())
            ->method('getTag')
            ->with($this->tagId)
            ->willReturn(null);

        $this->expectException(BlogNotFoundException::class);
        $this->expectExceptionMessage('This tag does not exist!');

        $this->service->changeTag($this->tagId, $this->newTagLabel);
    }

    /**
     * @throws BlogNotFoundException
     */
    public function testDeleteTagSuccess(): void
    {
        $this->tagRepository
            ->expects($this->once())
            ->method('getTag')
            ->with($this->tagId)
            ->willReturn($this->tag);

        $this->tagRepository
            ->expects($this->once())
            ->method('delete')
            ->with([$this->tag]);

        $this->service->delete($this->tagId);
    }

    public function testDeleteTagNotFound(): void
    {
        $this->tagRepository
            ->expects($this->once())
            ->method('getTag')
            ->with($this->tagId)
            ->willReturn(null);

        $this->expectException(BlogNotFoundException::class);
        $this->expectExceptionMessage('This tag does not exist!');


        $this->service->delete($this->tagId);
    }

    /**
     * @throws Exception
     */
    #[Override]
    protected function setUp(): void
    {
        $this->tagRepository = $this->createMock(TagRepositoryInterface::class);
        $this->service = new TagService($this->tagRepository);

        $this->tag = new Tag($this->tagLabel);
    }
}
