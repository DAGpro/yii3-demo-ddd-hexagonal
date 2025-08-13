<?php

declare(strict_types=1);

namespace App\Tests\Unit\Blog\Application\Service\AppService\QueryService;

use App\Blog\Application\Service\AppService\QueryService\ModeratePostQueryService;
use App\Blog\Domain\Port\PostRepositoryInterface;
use App\Blog\Domain\Post;
use App\Blog\Domain\User\Author;
use App\Blog\Infrastructure\Persistence\Post\PostRepository;
use Cycle\Database\Driver\DriverInterface;
use Cycle\ORM\EntityManagerInterface;
use Cycle\ORM\Select;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Yiisoft\Data\Reader\DataReaderInterface;


#[CoversClass(ModeratePostQueryService::class)]
final class ModeratePostQueryServiceTest extends TestCase
{
    private ModeratePostQueryService $service;

    private PostRepositoryInterface $postRepository;

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

        $query
            ->expects($this->once())
            ->method('load')
            ->with(['tags'])
            ->willReturn($query);

        $result = $this->service->findAllPreloaded();

        $this->assertInstanceOf(DataReaderInterface::class, $result);
    }

    public function testGetPostWhenPostExists(): void
    {
        $postId = 1;
        $post = new Post('Test Post', 'Test content', new Author(1, 'Test Author'));

        $query = $this->select;
        $query
            ->expects($this->exactly(2))
            ->method('__call')
            ->willReturnCallback(
                function ($method, $arguments) use ($query, $postId) {
                    if ($method === 'andWhere' && $arguments[0] === 'id') {
                        $this->assertIsArray($arguments);
                        $this->assertSame('id', $arguments[0]);
                        $this->assertSame('=', $arguments[1]);
                        $this->assertSame($postId, $arguments[2]);
                    }
                    if ($method === 'andWhere' && $arguments[0] === 'deleted_at') {
                        $this->assertIsArray($arguments);
                        $this->assertSame('deleted_at', $arguments[0]);
                        $this->assertSame('=', $arguments[1]);
                        $this->assertNull($arguments[2]);
                    }
                    return $query;
                },
            );

        $query
            ->expects($this->once())
            ->method('fetchOne')
            ->willReturn($post);

        $result = $this->service->getPost($postId);

        $this->assertSame($post, $result);
    }

    public function testGetPostWhenPostNotExists(): void
    {
        // Arrange
        $postId = 999;

        $query = $this->select;
        $query
            ->expects($this->exactly(2))
            ->method('__call')
            ->willReturnCallback(
                function ($method, $arguments) use ($query, $postId) {
                    if ($method === 'andWhere' && $arguments[0] === 'id') {
                        $this->assertIsArray($arguments);
                        $this->assertSame('id', $arguments[0]);
                        $this->assertSame('=', $arguments[1]);
                        $this->assertSame($postId, $arguments[2]);
                    }
                    if ($method === 'andWhere' && $arguments[0] === 'deleted_at') {
                        $this->assertIsArray($arguments);
                        $this->assertSame('deleted_at', $arguments[0]);
                        $this->assertSame('=', $arguments[1]);
                        $this->assertNull($arguments[2]);
                    }
                    return $query;
                },
            );

        $this->select
            ->expects($this->once())
            ->method('fetchOne')
            ->willReturn(null);

        $result = $this->service->getPost($postId);

        $this->assertNull($result);
    }

    /**
     * @throws Exception
     */
    #[Override]
    protected function setUp(): void
    {
        $this->postRepository = new PostRepository(
            $this->select = $this->createMock(Select::class),
            $this->createMock(EntityManagerInterface::class),
        );

        $this->service = new ModeratePostQueryService($this->postRepository);
    }
}
