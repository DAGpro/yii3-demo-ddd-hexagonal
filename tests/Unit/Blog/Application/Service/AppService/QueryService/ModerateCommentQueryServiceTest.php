<?php

declare(strict_types=1);

namespace App\Tests\Unit\Blog\Application\Service\AppService\QueryService;

use App\Blog\Domain\Comment;
use App\Blog\Domain\Port\CommentRepositoryInterface;
use App\Blog\Domain\Post;
use App\Blog\Domain\User\Author;
use App\Blog\Domain\User\Commentator;
use App\Blog\Slice\Comment\Application\Service\QueryService\ModerateCommentQueryService;
use App\Tests\UnitTester;
use Codeception\Test\Unit;
use Cycle\Database\Driver\DriverInterface;
use Cycle\ORM\Select;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use Yiisoft\Data\Cycle\Reader\EntityReader;

#[CoversClass(ModerateCommentQueryService::class)]
final class ModerateCommentQueryServiceTest extends Unit
{
    protected UnitTester $tester;

    private ModerateCommentQueryService $service;

    private CommentRepositoryInterface&MockObject $commentRepository;

    private Select&MockObject $select;

    public function testFindAllPreloaded(): void
    {
        $query = $this->select;
        $query
            ->expects($this->exactly(3))
            ->method('__call')
            ->willReturnCallback(
                function (string $method, array $arguments) use ($query) {
                    if ($method === 'getDriver') {
                        $driverMock = $this
                            ->createMock(DriverInterface::class);
                        $driverMock
                            ->expects($this->once())
                            ->method('getType')
                            ->willReturn('MySQL');
                        return $driverMock;
                    }
                    if ($method === 'orderBy') {
                        $this->assertEquals('orderBy', $method);
                        $this->assertEquals(
                            [
                                [
                                    'published_at' => 'ASC',
                                    'id' => 'DESC',
                                    'public' => 'ASC',
                                    'updated_at' => 'ASC',
                                    'post_id' => 'ASC',
                                ],
                            ],
                            $arguments,
                        );
                    }

                    return $query;
                },
            );
        $commentator = new Commentator(1, 'Commentator');
        $post = new Post('title', 'content', new Author(1, 'Author'));
        $selectResult = [
            new Comment('comment', $post, $commentator),
            new Comment('comment 2', $post, $commentator),
        ];
        $query
            ->expects($this->once())
            ->method('fetchAll')
            ->willReturn($selectResult);

        $this->commentRepository
            ->expects($this->once())
            ->method('findAllNonDeleted')
            ->willReturn(new EntityReader($query));

        $dataReader = $this->service->findAllPreloaded();

        $sortDataReader = $dataReader->getSort();
        $this->assertEquals(
            ['id' => 'asc', 'public' => 'asc', 'updated_at' => 'asc', 'published_at' => 'asc', 'post_id' => 'asc'],
            $sortDataReader->getDefaultOrder(),
        );
        $this->assertEquals(['id' => 'desc'], $sortDataReader->getOrder());
        $this->assertEquals($selectResult, $dataReader->read());
    }

    public function testGetCommentWhenCommentExists(): void
    {
        $commentId = 1;
        $comment = new Comment(
            "Test comment",
            new Post("Test", "Test content", new Author(1, 'Author')),
            new Commentator(1, 'Commentator'),
        );

        $this->commentRepository
            ->expects($this->once())
            ->method('getComment')
            ->with($commentId)
            ->willReturn($comment);

        $result = $this->service->getComment($commentId);

        $this->assertSame($comment, $result);
    }

    public function testGetCommentWhenCommentNotExists(): void
    {
        $commentId = 999;

        $this->commentRepository
            ->expects($this->once())
            ->method('getComment')
            ->with($commentId)
            ->willReturn(null);

        $result = $this->service->getComment($commentId);

        $this->assertNull($result);
    }

    /**
     * @throws Exception
     */
    #[Override]
    protected function _before(): void
    {
        $this->select = $this->createMock(Select::class);
        $this->commentRepository = $this->createMock(CommentRepositoryInterface::class);

        $this->service = new ModerateCommentQueryService(
            $this->commentRepository,
        );
    }
}
