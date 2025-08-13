<?php

declare(strict_types=1);

namespace App\Tests\Unit\Blog\Application\Service\AppService\CommandService;

use App\Blog\Application\Service\AppService\CommandService\ModerateCommentService;
use App\Blog\Application\Service\QueryService\ModerateCommentQueryServiceInterface;
use App\Blog\Domain\Comment;
use App\Blog\Domain\Exception\BlogNotFoundException;
use App\Blog\Domain\Port\CommentRepositoryInterface;
use App\Blog\Domain\Post;
use App\Blog\Domain\User\Author;
use App\Blog\Domain\User\Commentator;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(ModerateCommentService::class)]
final class ModerateCommentServiceTest extends TestCase
{
    private ModerateCommentService $service;

    private CommentRepositoryInterface&MockObject $commentRepository;

    private ModerateCommentQueryServiceInterface&MockObject $commentQueryService;

    private int $commentId = 1;
    private string $commentText = 'Test comment';
    private Comment $comment;

    /**
     * @throws BlogNotFoundException
     */
    public function testDraftCommentSuccess(): void
    {
        $this->commentQueryService
            ->expects($this->once())
            ->method('getComment')
            ->with($this->commentId)
            ->willReturn($this->comment);

        $this->commentRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function ($comments) {
                $this->assertIsArray($comments);
                $this->assertCount(1, $comments);
                $comment = $comments[0];
                $this->assertFalse($comment->isPublic());
                return true;
            }),
            );

        $this->service->draft($this->commentId);
    }

    /**
     * @throws BlogNotFoundException
     */
    public function testPublicCommentWhenAlreadyPublic(): void
    {
        $this->comment->publish();

        $this->commentQueryService
            ->expects($this->once())
            ->method('getComment')
            ->with($this->commentId)
            ->willReturn($this->comment);

        $this->commentRepository
            ->expects($this->once())
            ->method('save')
            ->with([$this->comment]);

        $this->service->public($this->commentId);
    }

    /**
     * @throws BlogNotFoundException
     */
    public function testPublicCommentWhenInDraft(): void
    {
        $this->comment->toDraft();

        $this->commentQueryService
            ->expects($this->once())
            ->method('getComment')
            ->with($this->commentId)
            ->willReturn($this->comment);

        $this->commentRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function ($comments) {
                $this->assertTrue($comments[0]->isPublic());
                return true;
            }),
            );

        $this->service->public($this->commentId);
    }

    /**
     * @throws BlogNotFoundException
     */
    public function testModerateCommentWithPublish(): void
    {
        $newText = 'Updated comment text';

        $this->commentQueryService
            ->expects($this->once())
            ->method('getComment')
            ->with($this->commentId)
            ->willReturn($this->comment);

        $this->commentRepository
            ->expects($this->once())
            ->method('save')
            ->with(
                $this->callback(
                /** @var iterable<Comment> $comments */
                    function (array $comments) use ($newText) {
                        $this->assertEquals($newText, $comments[0]->getContent());
                        $this->assertTrue($comments[0]->isPublic());
                        return true;
                    },
                ),
            );

        $this->service->moderate($this->commentId, $newText, true);
    }

    /**
     * @throws BlogNotFoundException
     */
    public function testModerateCommentWithDraft(): void
    {
        $newText = 'Updated comment text';
        $this->comment->publish();

        $this->commentQueryService
            ->expects($this->once())
            ->method('getComment')
            ->with($this->commentId)
            ->willReturn($this->comment);

        $this->commentRepository
            ->expects($this->once())
            ->method('save')
            ->with(
            /** @var iterable<Comment> $comments */
                $this->callback(function (array $comments) use ($newText) {
                    $this->assertEquals($newText, $comments[0]->getContent());
                    $this->assertFalse($comments[0]->isPublic());
                    return true;
                }),
            );

        $this->service->moderate($this->commentId, $newText, false);
    }

    /**
     * @throws BlogNotFoundException
     */
    public function testDeleteCommentSuccess(): void
    {
        $this->commentQueryService
            ->expects($this->once())
            ->method('getComment')
            ->with($this->commentId)
            ->willReturn($this->comment);

        $this->commentRepository
            ->expects($this->once())
            ->method('delete')
            ->with([$this->comment]);

        $this->service->delete($this->commentId);
    }

    public function testCommentNotFound(): void
    {
        $this->commentQueryService
            ->expects($this->once())
            ->method('getComment')
            ->with($this->commentId)
            ->willReturn(null);

        $this->expectException(BlogNotFoundException::class);
        $this->expectExceptionMessage('Comment does not exist!');

        $this->service->delete($this->commentId);
    }

    /**
     * @throws Exception
     */
    #[Override]
    protected function setUp(): void
    {
        $this->commentRepository = $this->createMock(CommentRepositoryInterface::class);
        $this->commentQueryService = $this->createMock(ModerateCommentQueryServiceInterface::class);

        $this->service = new ModerateCommentService(
            $this->commentRepository,
            $this->commentQueryService,
        );

        $this->comment = new Comment(
            $this->commentText,
            new Post('Test Post', 'Test Content', new Author(1, 'Test Author')),
            new Commentator(1, 'Test Commentator'),
        );
    }
}
