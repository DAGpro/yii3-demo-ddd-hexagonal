<?php

declare(strict_types=1);

namespace App\Tests\Unit\Blog\Domain;

use App\Blog\Domain\Comment;
use App\Blog\Domain\Post;
use App\Blog\Domain\User\Commentator;
use DateTimeImmutable;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

#[CoversClass(Comment::class)]
final class CommentTest extends TestCase
{
    private Comment $comment;
    private Post $post;
    private Commentator $commentator;
    private string $content = 'Test comment content';

    public function testCreate(): void
    {
        $this->assertNull($this->comment->getId());
        $this->assertSame($this->content, $this->comment->getContent());
        $this->assertSame($this->post, $this->comment->getPost());
        $this->assertSame($this->commentator, $this->comment->getCommentator());
        $this->assertFalse($this->comment->isPublic());
        $this->assertNull($this->comment->getPublishedAt());
        $this->assertInstanceOf(DateTimeImmutable::class, $this->comment->getCreatedAt());
        $this->assertInstanceOf(DateTimeImmutable::class, $this->comment->getUpdatedAt());
    }

    public function testPublish(): void
    {
        $this->comment->publish();

        $this->assertTrue($this->comment->isPublic());
        $this->assertInstanceOf(DateTimeImmutable::class, $this->comment->getPublishedAt());
    }

    public function testToDraft(): void
    {
        // Сначала публикуем комментарий
        $this->comment->publish();
        $this->assertTrue($this->comment->isPublic());

        // Затем переводим в черновик
        $this->comment->toDraft();

        $this->assertFalse($this->comment->isPublic());
        $this->assertNull($this->comment->getPublishedAt());
    }

    public function testChangeContent(): void
    {
        $newContent = 'Updated comment content';
        $this->comment->change($newContent);

        $this->assertSame($newContent, $this->comment->getContent());
    }

    public function testChangePost(): void
    {
        $newPost = $this->createMock(Post::class);
        $this->comment->change('', $newPost);

        $this->assertSame($newPost, $this->comment->getPost());
    }

    public function testChangeCommentator(): void
    {
        $newCommentator = $this->createMock(Commentator::class);
        $this->comment->change('', null, $newCommentator);

        $this->assertSame($newCommentator, $this->comment->getCommentator());
    }

    public function testIsCommentator(): void
    {
        $this->commentator
            ->method('isEqual')
            ->with($this->commentator)
            ->willReturn(true);

        $this->assertTrue($this->comment->isCommentator($this->commentator));
    }

    public function testSetPublishedAt(): void
    {
        $date = new DateTimeImmutable('2023-01-01');
        $this->comment->setPublishedAt($date);

        $this->assertSame($date, $this->comment->getPublishedAt());
    }

    public function testGetDeletedAt(): void
    {
        $this->assertNull($this->comment->getDeletedAt());
    }

    /**
     * @throws Exception
     */
    #[Override]
    protected function setUp(): void
    {
        $this->post = $this->createMock(Post::class);
        $this->commentator = $this->createMock(Commentator::class);

        $this->comment = new Comment(
            $this->content,
            $this->post,
            $this->commentator,
        );
    }
}
