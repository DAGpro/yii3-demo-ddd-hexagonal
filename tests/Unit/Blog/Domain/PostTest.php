<?php

declare(strict_types=1);

namespace App\Tests\Unit\Blog\Domain;

use App\Blog\Domain\Post;
use App\Blog\Domain\Tag;
use App\Blog\Domain\User\Author;
use App\Blog\Domain\User\Commentator;
use App\Tests\UnitTester;
use Codeception\Test\Unit;
use DateTimeImmutable;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;

#[CoversClass(Post::class)]
final class PostTest extends Unit
{
    protected UnitTester $tester;

    private Post $post;

    private Author&MockObject $author;

    private string $title = 'Test Post Title';

    private string $content = 'Test Post Content';

    private string $updatedTitle = 'Updated Post Title';

    private string $updatedContent = 'Updated Post Content';

    public function testCreate(): void
    {
        $this->assertNull($this->post->getId());
        $this->assertSame($this->title, $this->post->getTitle());
        $this->assertSame($this->content, $this->post->getContent());
        $this->assertSame($this->author, $this->post->getAuthor());
        $this->assertNotEmpty($this->post->getSlug());
        $this->assertFalse($this->post->isPublic());
        $this->assertNull($this->post->getPublishedAt());
        $this->assertInstanceOf(DateTimeImmutable::class, $this->post->getCreatedAt());
        $this->assertInstanceOf(DateTimeImmutable::class, $this->post->getUpdatedAt());
        $this->assertEmpty($this->post->getTags());
        $this->assertEmpty($this->post->getComments());
    }

    public function testEdit(): void
    {
        $newAuthor = $this->createMock(Author::class);
        $this->post->edit($this->updatedTitle, $this->updatedContent, $newAuthor);

        $this->assertSame($this->updatedTitle, $this->post->getTitle());
        $this->assertSame($this->updatedContent, $this->post->getContent());
        $this->assertSame($newAuthor, $this->post->getAuthor());
    }

    public function testEditWithoutAuthor(): void
    {
        $this->post->edit($this->updatedTitle, $this->updatedContent);

        $this->assertSame($this->updatedTitle, $this->post->getTitle());
        $this->assertSame($this->updatedContent, $this->post->getContent());
        $this->assertSame($this->author, $this->post->getAuthor()); // Автор не должен измениться
    }

    public function testPublish(): void
    {
        $this->post->publish();

        $this->assertTrue($this->post->isPublic());
        $this->assertInstanceOf(DateTimeImmutable::class, $this->post->getPublishedAt());
    }

    public function testToDraft(): void
    {
        $this->post->publish();
        $this->assertTrue($this->post->isPublic());

        $this->post->toDraft();

        $this->assertFalse($this->post->isPublic());
        $this->assertNull($this->post->getPublishedAt());
    }

    public function testIsAuthor(): void
    {
        $this->author
            ->method('isEqual')
            ->with($this->author)
            ->willReturn(true);

        $this->assertTrue($this->post->isAuthor($this->author));
    }

    public function testChangeAuthor(): void
    {
        $newAuthor = $this->createMock(Author::class);
        $this->post->changeAuthor($newAuthor);

        $this->assertSame($newAuthor, $this->post->getAuthor());
    }

    public function testCreateComment(): void
    {
        $commentator = $this->createMock(Commentator::class);
        $commentText = 'Test comment';
        $comment = $this->post->createComment($commentText, $commentator);

        $this->assertSame($commentText, $comment->getContent());
        $this->assertSame($this->post, $comment->getPost());
        $this->assertSame($commentator, $comment->getCommentator());
    }

    public function testAddAndGetTags(): void
    {
        $tag1 = new Tag('tag1');
        $tag2 = new Tag('tag2');

        $this->post->addTag($tag1);
        $this->post->addTag($tag2);

        $tags = $this->post->getTags();

        $this->assertCount(2, $tags);
        $this->assertContains($tag1, $tags);
        $this->assertContains($tag2, $tags);
    }

    public function testResetTags(): void
    {
        $tag1 = new Tag('tag1');
        $this->post->addTag($tag1);

        $this->assertCount(1, $this->post->getTags());

        $this->post->resetTags();

        $this->assertEmpty($this->post->getTags());
    }

    public function testResetSlug(): void
    {
        $originalSlug = $this->post->getSlug();
        $this->post->resetSlug();

        $this->assertNotSame($originalSlug, $this->post->getSlug());
        $this->assertNotEmpty($this->post->getSlug());
    }

    public function testSetPublishedAt(): void
    {
        $date = new DateTimeImmutable('2023-01-01');
        $this->post->setPublishedAt($date);

        $this->assertSame($date, $this->post->getPublishedAt());
    }

    public function testIsNewRecord(): void
    {
        // New Post (not yet saved in the database)
        $this->assertTrue($this->post->isNewRecord());

        // emulate the preserved post
        $reflection = new ReflectionClass($this->post);
        $property = $reflection->getProperty('id');
        $property->setValue($this->post, 1);

        $this->assertFalse($this->post->isNewRecord());
    }

    public function testNotDeletedPost(): void
    {
        $this->assertNull($this->post->getDeletedAt());
    }

    /**
     * @throws Exception
     */
    #[Override]
    protected function _before(): void
    {
        $this->author = $this->createMock(Author::class);
        $this->post = new Post(
            $this->title,
            $this->content,
            $this->author,
        );
    }
}
