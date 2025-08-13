<?php

declare(strict_types=1);

namespace App\Tests\Unit\Blog\Application\Service\AppService\QueryService;

use App\Blog\Application\Service\AppService\QueryService\ModerateCommentQueryService;
use App\Blog\Domain\Comment;
use App\Blog\Domain\Port\CommentRepositoryInterface;
use App\Blog\Domain\Post;
use App\Blog\Domain\User\Author;
use App\Blog\Domain\User\Commentator;
use App\Blog\Infrastructure\Persistence\Comment\CommentRepository;
use Cycle\Database\Driver\DriverInterface;
use Cycle\ORM\EntityManagerInterface;
use Cycle\ORM\Select;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Yiisoft\Data\Reader\DataReaderInterface;

#[CoversClass(ModerateCommentQueryService::class)]
final class ModerateCommentQueryServiceTest extends TestCase
{
    private ModerateCommentQueryService $service;

    private CommentRepositoryInterface $commentRepository;

    private Select&MockObject $select;

    public function testFindAllPreloaded(): void
    {
        $query = $this->select;
        $query
            ->expects($this->exactly(2))
            ->method('__call')
            ->willReturnCallback(
                function ($method, $arguments) use ($query) {
                    if ($method === 'getDriver') {
                        $driverMock = $this
                            ->createMock(DriverInterface::class);
                        $driverMock
                            ->expects($this->once())
                            ->method('getType')
                            ->willReturn('MySQL');
                        return $driverMock;
                    }
                    if ($method === 'andWhere') {
                        $this->assertIsArray($arguments);
                        $this->assertSame('deleted_at', $arguments[0]);
                        $this->assertSame('=', $arguments[1]);
                        $this->assertNull($arguments[2]);
                    }
                    return $query;
                },
            );

        $result = $this->service->findAllPreloaded();

        $this->assertInstanceOf(DataReaderInterface::class, $result);
    }

    public function testGetCommentWhenCommentExists(): void
    {
        $commentId = 1;
        $comment = new Comment(
            "Test comment",
            new Post("Test", "Test content", new Author(1, 'Author')),
            new Commentator(1, 'Commentator'),
        );

        $this->select
            ->expects($this->once())
            ->method('__call')
            ->with('where', [['id' => $commentId]])
            ->willReturn($this->select);

        $this->select
            ->expects($this->once())
            ->method('fetchOne')
            ->willReturn($comment);

        $result = $this->service->getComment($commentId);

        $this->assertSame($comment, $result);
    }

    public function testGetCommentWhenCommentNotExists(): void
    {
        $commentId = 999;

        $this->select
            ->expects($this->once())
            ->method('__call')
            ->with('where', [['id' => $commentId]])
            ->willReturn($this->select);

        $this->select
            ->expects($this->once())
            ->method('fetchOne')
            ->willReturn(null);

        $result = $this->service->getComment($commentId);

        $this->assertNull($result);
    }

    /**
     * @throws Exception
     */
    #[Override]
    protected function setUp(): void
    {
        $this->commentRepository = new CommentRepository(
            $this->select = $this->createMock(Select::class),
            $this->createMock(EntityManagerInterface::class),
        );

        $this->service = new ModerateCommentQueryService($this->commentRepository);
    }
}
