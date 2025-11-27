<?php

declare(strict_types=1);

namespace App\Tests\Unit\Blog\Slice\Post\Service\CommandService;

use App\Blog\Slice\Post\Service\CommandService\PostChangeDTO;
use Codeception\Test\Unit;
use Error;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PostChangeDTO::class)]
final class PostChangeDTOTest extends Unit
{
    private const string TITLE = 'Test Title';
    private const string CONTENT = 'Test Content';
    private const array TAGS = ['tag1', 'tag2'];

    private PostChangeDTO $dto;

    public function testGetTitle(): void
    {
        $this->assertSame(self::TITLE, $this->dto->getTitle());
    }

    public function testGetContent(): void
    {
        $this->assertSame(self::CONTENT, $this->dto->getContent());
    }

    public function testGetTags(): void
    {
        $this->assertSame(self::TAGS, $this->dto->getTags());
    }

    public function testImmutability(): void
    {
        $this->expectException(Error::class);
        $this->dto->title = 'New Title';
    }

    #[Override]
    protected function _before(): void
    {
        $this->dto = new PostChangeDTO(
            self::TITLE,
            self::CONTENT,
            self::TAGS,
        );
    }
}
