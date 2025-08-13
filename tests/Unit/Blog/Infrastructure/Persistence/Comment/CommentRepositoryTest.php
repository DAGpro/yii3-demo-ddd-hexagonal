<?php

declare(strict_types=1);

namespace App\Tests\Unit\Blog\Infrastructure\Persistence\Comment;

use App\Blog\Domain\Comment;
use App\Blog\Infrastructure\Persistence\Comment\CommentRepository;
use Cycle\ORM\EntityManagerInterface;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\Select;
use Override;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;

class CommentRepositoryTest extends TestCase
{
    private CommentRepository $repository;
    private Select|MockObject $select;
    private EntityManagerInterface|MockObject $entityManager;
    private ORMInterface|MockObject $orm;

    public function testSelect(): void
    {
        $select = $this->repository->select();
        $this->assertInstanceOf(Select::class, $select);
    }

    /**
     * @throws Exception
     */
    public function testGetPublicComment(): void
    {
        $commentId = 1;
        $expectedComment = $this->createMock(Comment::class);

        $this->select
            ->expects($this->once())
            ->method('fetchOne')
            ->with(['id' => $commentId])
            ->willReturn($expectedComment);

        $result = $this->repository->getPublicComment($commentId);

        $this->assertSame($expectedComment, $result);
    }

    public function testGetPublicCommentNotFound(): void
    {
        $commentId = 999;

        $this->select
            ->expects($this->once())
            ->method('fetchOne')
            ->with(['id' => $commentId])
            ->willReturn(null);

        $result = $this->repository->getPublicComment($commentId);

        $this->assertNull($result);
    }

    /**
     * @throws Exception
     */
    public function testGetComment(): void
    {
        $commentId = 1;
        $expectedComment = $this->createMock(Comment::class);

        $selectMock = $this->select;
        $selectMock
            ->expects($this->once())
            ->method('__call')
            ->with('where', [['id' => $commentId]])
            ->willReturnSelf();

        $selectMock
            ->expects($this->once())
            ->method('fetchOne')
            ->willReturn($expectedComment);

        $result = $this->repository->getComment($commentId);

        $this->assertSame($expectedComment, $result);
    }

    public function testGetCommentNotFound(): void
    {
        $commentId = 999;

        $selectMock = $this->select;
        $selectMock
            ->expects($this->once())
            ->method('__call')
            ->with('where', [['id' => $commentId]])
            ->willReturnSelf();

        $selectMock
            ->expects($this->once())
            ->method('fetchOne')
            ->willReturn(null);

        $result = $this->repository->getComment($commentId);

        $this->assertNull($result);
    }

    public function testSaveWithEmptyArray(): void
    {
        $this->entityManager
            ->expects($this->never())
            ->method('persist');

        $this->entityManager
            ->expects($this->never())
            ->method('run');

        $this->repository->save([]);
    }

    /**
     * @throws Exception
     */
    public function testSaveWithComments(): void
    {
        $comment1 = $this->createMock(Comment::class);
        $comment2 = $this->createMock(Comment::class);
        $nonComment = new stdClass();

        $this->entityManager
            ->expects($this->exactly(2))
            ->method('persist')
            ->willReturnMap([
                [$comment1, true, $this->entityManager],
                [$comment2, true, $this->entityManager],
            ]);

        $this->entityManager
            ->expects($this->once())
            ->method('run');

        $this->repository->save([$comment1, $nonComment, $comment2]);
    }

    public function testDeleteWithEmptyArray(): void
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
    public function testDeleteWithComments(): void
    {
        $comment1 = $this->createMock(Comment::class);
        $comment2 = $this->createMock(Comment::class);
        $nonComment = new stdClass();

        $this->entityManager
            ->expects($this->exactly(2))
            ->method('delete')
            ->willReturnMap([
                [$comment1, true, $this->entityManager],
                [$comment2, true, $this->entityManager],
            ]);

        $this->entityManager
            ->expects($this->once())
            ->method('run');

        $this->repository->delete([$comment1, $nonComment, $comment2]);
    }

    /**
     * @throws Exception
     */
    #[Override]
    protected function setUp(): void
    {
        $this->select = $this->createMock(Select::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->repository = new CommentRepository(
            $this->select,
            $this->entityManager,
        );
    }
}
