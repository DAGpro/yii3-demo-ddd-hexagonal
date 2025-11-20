<?php

declare(strict_types=1);

namespace App\Tests\Unit\Blog\Domain;

use App\Blog\Domain\Comment;
use App\Blog\Domain\Post;
use App\Blog\Domain\User\Commentator;
use App\Tests\UnitTester;
use Codeception\Test\Unit;
use DateTimeImmutable;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(Comment::class)]
final class CommentTest extends Unit
{
    protected UnitTester $tester;

    private Comment $comment;

    private Post&MockObject $post;

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
        $this->comment->publish();
        $this->assertTrue($this->comment->isPublic());

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
        $newCommentator = new Commentator(2, 'new name');
        $this->comment->changeCommentator($newCommentator);

        $this->assertSame($newCommentator, $this->comment->getCommentator());
    }

    public function testIsCommentator(): void
    {
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
    protected function _before(): void
    {
        $this->post = $this->createMock(Post::class);
        $this->commentator = new Commentator(1, 'name');

        $this->comment = new Comment(
            $this->content,
            $this->post,
            $this->commentator,
        );
    }
}
