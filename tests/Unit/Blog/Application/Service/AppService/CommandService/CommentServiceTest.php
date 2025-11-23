<?php

declare(strict_types=1);

namespace App\Tests\Unit\Blog\Application\Service\AppService\CommandService;

use App\Blog\Application\Service\AppService\CommandService\CommentService;
use App\Blog\Application\Service\QueryService\CommentQueryServiceInterface;
use App\Blog\Application\Service\QueryService\ReadPostQueryServiceInterface;
use App\Blog\Domain\Comment;
use App\Blog\Domain\Exception\BlogNotFoundException;
use App\Blog\Domain\Port\CommentRepositoryInterface;
use App\Blog\Domain\Post;
use App\Blog\Domain\User\Author;
use App\Blog\Domain\User\Commentator;
use App\Tests\UnitTester;
use Codeception\Test\Unit;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(CommentService::class)]
final class CommentServiceTest extends Unit
{
    protected UnitTester $tester;

    private CommentService $service;

    private CommentRepositoryInterface&MockObject $commentRepository;

    private ReadPostQueryServiceInterface&MockObject $postQueryService;

    private CommentQueryServiceInterface&MockObject $commentQueryService;

    private Commentator $commentator;

    private int $postId = 1;

    private int $commentId = 1;

    private string $commentText = 'Test comment';

    /**
     * @throws BlogNotFoundException
     */
    public function testAddCommentSuccess(): void
    {
        $post = new Post('Test Post', 'Test Content', new Author(1, 'Test Author'));

        $this->postQueryService
            ->expects($this->once())
            ->method('getPost')
            ->with($this->postId)
            ->willReturn($post);

        $this->commentRepository
            ->expects($this->once())
            ->method('save')
            ->with(
                $this->callback(
                    function ($comments) {
                        $this->assertIsArray($comments);
                        $this->assertCount(1, $comments);
                        $comment = $comments[0];
                        $this->assertInstanceOf(Comment::class, $comment);
                        $this->assertEquals($this->commentText, $comment->getContent());
                        return true;
                    },
                ),
            );

        $this->service->add($this->postId, $this->commentText, $this->commentator);
    }

    public function testAddCommentPostNotFound(): void
    {
        $this->postQueryService
            ->expects($this->once())
            ->method('getPost')
            ->with($this->postId)
            ->willReturn(null);

        $this->expectException(BlogNotFoundException::class);
        $this->expectExceptionMessage('Post does not exist!');

        $this->service->add($this->postId, $this->commentText, $this->commentator);
    }

    /**
     * @throws BlogNotFoundException
     */
    public function testEditCommentSuccess(): void
    {
        $newCommentText = 'Updated comment';
        $comment = new Comment(
            $this->commentText,
            new Post('Test Post', 'Test Content', new Author(1, 'Test Author')),
            $this->commentator,
        );

        $this->commentQueryService
            ->expects($this->once())
            ->method('getComment')
            ->with($this->commentId)
            ->willReturn($comment);

        $this->commentRepository
            ->expects($this->once())
            ->method('save')
            ->with(
                /** @var iterable<Comment> $comments */
                $this->callback(
                    function (array $comments) use ($newCommentText) {
                        $this->assertIsArray($comments);
                        $this->assertCount(1, $comments);
                        $updatedComment = $comments[0];
                        $this->assertEquals($newCommentText, $updatedComment->getContent());
                        return true;
                    },
                ),
            );

        $this->service->edit($this->commentId, $newCommentText);
    }

    public function testEditCommentNotFound(): void
    {
        $newCommentText = 'Updated comment';

        $this->commentQueryService
            ->expects($this->once())
            ->method('getComment')
            ->with($this->commentId)
            ->willReturn(null);

        $this->expectException(BlogNotFoundException::class);
        $this->expectExceptionMessage('Comment does not exist!');

        $this->service->edit($this->commentId, $newCommentText);
    }

    #[Override]
    protected function _before(): void
    {
        $this->commentRepository = $this->createMock(CommentRepositoryInterface::class);
        $this->postQueryService = $this->createMock(ReadPostQueryServiceInterface::class);
        $this->commentQueryService = $this->createMock(CommentQueryServiceInterface::class);

        $this->service = new CommentService(
            $this->commentRepository,
            $this->postQueryService,
            $this->commentQueryService,
        );

        $this->commentator = new Commentator(1, 'Test Commentator');
    }
}
