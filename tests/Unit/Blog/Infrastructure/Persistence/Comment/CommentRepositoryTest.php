<?php

declare(strict_types=1);

namespace App\Tests\Unit\Blog\Infrastructure\Persistence\Comment;

use App\Blog\Domain\Comment;
use App\Blog\Domain\Port\CommentRepositoryInterface;
use App\Blog\Slice\Comment\Infrastructure\Repository\CommentRepository;
use App\Tests\UnitTester;
use Codeception\Test\Unit;
use Cycle\Database\Driver\DriverInterface;
use Cycle\ORM\EntityManagerInterface;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\Select;
use Override;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use Yiisoft\Data\Reader\DataReaderInterface;

class CommentRepositoryTest extends Unit
{
    protected UnitTester $tester;

    private CommentRepositoryInterface $repository;

    private Select&MockObject $select;

    private EntityManagerInterface&MockObject $entityManager;

    private readonly ORMInterface&MockObject $orm;

    /**
     * @throws Exception
     */
    public function testFindAllNonDeleted(): void
    {
        $this->select
            ->expects($this->once())
            ->method('scope')
            ->willReturn($this->select);

        $this->select
            ->expects($this->exactly(2))
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
                    $this->assertIsArray($arguments);
                    $this->assertSame('deleted_at', $arguments[0]);
                    $this->assertSame('=', $arguments[1]);
                    $this->assertNull($arguments[2]);
                    return $this->select;
                },
            );

        $result = $this->repository->findAllNonDeleted();

        $this->assertInstanceOf(DataReaderInterface::class, $result);
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
            ->method('scope')
            ->willReturn($selectMock);

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
            ->method('scope')
            ->willReturn($selectMock);

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

        $this->repository->save([$comment1, $comment2]);
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

        $this->repository->delete([$comment1, $comment2]);
    }

    /**
     * @throws Exception
     */
    #[Override]
    protected function _before(): void
    {
        $this->select = $this->createMock(Select::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->repository = new CommentRepository(
            $this->select,
            $this->entityManager,
        );
    }
}
