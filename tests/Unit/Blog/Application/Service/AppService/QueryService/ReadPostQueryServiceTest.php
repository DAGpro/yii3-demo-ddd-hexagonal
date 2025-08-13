<?php

declare(strict_types=1);

namespace App\Tests\Unit\Blog\Application\Service\AppService\QueryService;

use App\Blog\Application\Service\AppService\QueryService\ReadPostQueryService;
use App\Blog\Domain\Port\PostRepositoryInterface;
use App\Blog\Domain\Post;
use App\Blog\Domain\Tag;
use App\Blog\Domain\User\Author;
use App\Blog\Infrastructure\Persistence\Post\PostRepository;
use Cycle\Database\Driver\DriverInterface;
use Cycle\ORM\EntityManagerInterface;
use Cycle\ORM\Select;
use DateTimeImmutable;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Yiisoft\Data\Reader\DataReaderInterface;

#[CoversClass(ReadPostQueryService::class)]
final class ReadPostQueryServiceTest extends TestCase
{
    private ReadPostQueryService $service;

    private PostRepositoryInterface $postRepository;

    private Select&MockObject $select;

    public function testFindAllPreloaded(): void
    {
        $this->select
            ->expects($this->once())
            ->method('load')
            ->with(['tags'])
            ->willReturn($this->select);

        $result = $this->service->findAllPreloaded();

        $this->assertInstanceOf(DataReaderInterface::class, $result);
    }

    public function testFindByTag(): void
    {
        // Arrange
        $tag = new Tag('test-tag');

        $this->select
            ->expects($this->once())
            ->method('load')
            ->with(['tags'])
            ->willReturn($this->select);

        $this->select
            ->expects($this->exactly(2))
            ->method('__call')
            ->willReturnCallback(
                function (mixed $method, mixed $arguments) {
                    if ($method === 'getDriver') {
                        $driverMock = $this
                            ->createMock(DriverInterface::class);
                        $driverMock
                            ->expects($this->once())
                            ->method('getType')
                            ->willReturn('MySQL');
                        return $driverMock;
                    }
                    if ($method === 'where') {
                        $this->assertIsArray($arguments);
                        $this->assertArrayHasKey('tags.label', $arguments[0]);
                        $this->assertSame('test-tag', $arguments[0]['tags.label']);
                        return $this->select;
                    }
                    return $this->select;
                },
            );

        $result = $this->service->findByTag($tag);

        $this->assertInstanceOf(DataReaderInterface::class, $result);
    }

    /**
     * @throws Exception
     */
    public function testFindByAuthor(): void
    {
        $author = $this->createMock(Author::class);
        $author->method('getId')->willReturn(1);

        $this->select
            ->expects($this->once())
            ->method('load')
            ->with(['tags'])
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
                            ->willReturn('MySQL');
                        return $driverMock;
                    }
                    if ($method === 'where') {
                        $this->assertIsArray($arguments);
                        $this->assertArrayHasKey('author_id', $arguments[0]);
                        $this->assertSame(1, $arguments[0]['author_id']);
                        return $this->select;
                    }
                    return $this->select;
                },
            );

        $result = $this->service->findByAuthor($author);

        $this->assertInstanceOf(DataReaderInterface::class, $result);
    }

    public function testGetPostBySlug(): void
    {
        $slug = 'test-post';
        $post = $this->createMock(Post::class);

        $this->select
            ->expects($this->once())
            ->method('__call')
            ->with('where', [['slug' => $slug]])
            ->willReturn($this->select);

        $this->select
            ->expects($this->once())
            ->method('load')
            ->with(['tags'])
            ->willReturn($this->select);

        $this->select
            ->expects($this->once())
            ->method('fetchOne')
            ->willReturn($post);

        $result = $this->service->getPostBySlug($slug);

        $this->assertSame($post, $result);
    }

    /**
     * @throws Exception
     */
    public function testGetPostById(): void
    {
        $postId = 1;
        $post = $this->createMock(Post::class);

        $this->select
            ->expects($this->once())
            ->method('__call')
            ->with('where', [['id' => $postId]])
            ->willReturn($this->select);

        $this->select
            ->expects($this->once())
            ->method('load')
            ->with(['tags'])
            ->willReturn($this->select);

        $this->select
            ->expects($this->once())
            ->method('fetchOne')
            ->willReturn($post);

        $result = $this->service->getPost($postId);

        $this->assertSame($post, $result);
    }

    /**
     * @throws Exception
     */
    public function testFullPostPage(): void
    {
        $slug = 'test-post';
        $post = $this->createMock(Post::class);

        $this->select
            ->expects($this->once())
            ->method('__call')
            ->with('where', [['slug' => $slug]])
            ->willReturn($this->select);

        $this->select
            ->expects($this->exactly(2))
            ->method('load')
            ->willReturnOnConsecutiveCalls(
                [['tags']],
                [
                    'comments',
                    [
                        'method' => Select::OUTER_QUERY,
                        'where' => ['public' => true],
                    ],
                ],
            )
            ->willReturn($this->select);

        $this->select
            ->expects($this->once())
            ->method('fetchOne')
            ->willReturn($post);

        $result = $this->service->fullPostPage($slug);

        $this->assertSame($post, $result);
    }

    public function testGetMaxUpdatedAt(): void
    {
        $maxUpdatedAt = '2023-01-01 12:00:00';

        $this->select
            ->expects($this->once())
            ->method('__call')
            ->with('max', ['updated_at'])
            ->willReturn($maxUpdatedAt);

        $result = $this->service->getMaxUpdatedAt();

        $this->assertInstanceOf(DateTimeImmutable::class, $result);
        $this->assertEquals($maxUpdatedAt, $result->format('Y-m-d H:i:s'));
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

        $this->service = new ReadPostQueryService($this->postRepository);
    }
}
