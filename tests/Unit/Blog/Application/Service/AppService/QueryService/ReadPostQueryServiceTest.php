<?php

declare(strict_types=1);

namespace App\Tests\Unit\Blog\Application\Service\AppService\QueryService;

use App\Blog\Domain\Port\PostRepositoryInterface;
use App\Blog\Domain\Post;
use App\Blog\Domain\Tag;
use App\Blog\Domain\User\Author;
use App\Blog\Slice\Post\Service\QueryService\ReadPostQueryService;
use App\Tests\UnitTester;
use Codeception\Test\Unit;
use Cycle\Database\Driver\DriverInterface;
use Cycle\ORM\Select;
use DateMalformedStringException;
use DateTimeImmutable;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use Yiisoft\Data\Cycle\Reader\EntityReader;

#[CoversClass(ReadPostQueryService::class)]
final class ReadPostQueryServiceTest extends Unit
{
    protected UnitTester $tester;

    private ReadPostQueryService $service;

    private PostRepositoryInterface&MockObject $repository;

    private Select&MockObject $select;

    public function testFindAllPreloaded(): void
    {
        $author = $this->createMock(Author::class);
        $tag = new Tag('test-tag');
        $tag2 = new Tag('test-tag-2');
        $post = new Post('title', 'content', $author);
        $post->addTag($tag);
        $post2 = new Post('title 2', 'content 2', $author);
        $post2->addTag($tag2);
        $selectResult = [
            $post,
            $post2,
        ];

        $select = $this->createMockSelectForReader(
            [
                [
                    'published_at' => 'DESC',
                    'id' => 'ASC',
                    'title' => 'ASC',
                    'public' => 'ASC',
                    'updated_at' => 'ASC',
                ],
            ],
            $selectResult,
        );

        $this->repository
            ->expects($this->once())
            ->method('getAllWithPreloadedTags')
            ->willReturn(new EntityReader($select));

        $dataReader = $this->service->findAllPreloaded();

        $sortDataReader = $dataReader->getSort();
        $this->assertEquals(
            ['id' => 'asc', 'title' => 'asc', 'public' => 'asc', 'updated_at' => 'asc', 'published_at' => 'asc'],
            $sortDataReader->getDefaultOrder(),
        );
        $this->assertEquals(['published_at' => 'desc'], $sortDataReader->getOrder());
        $this->assertEquals($selectResult, $dataReader->read());
    }

    public function testFindByTag(): void
    {
        $tag = new Tag('test-tag');

        $author = $this->createMock(Author::class);
        $post = new Post('title', 'content', $author);
        $post->addTag($tag);
        $post2 = new Post('title 2', 'content 2', $author);
        $post2->addTag($tag);
        $selectResult = [
            $post,
            $post2,
        ];

        $select = $this->createMockSelectForReader(
            [
                [
                    'published_at' => 'DESC',
                    'id' => 'ASC',
                    'title' => 'ASC',
                    'public' => 'ASC',
                    'updated_at' => 'ASC',
                ],
            ],
            $selectResult,
        );

        $this->repository
            ->expects($this->once())
            ->method('findByTagWithPreloadedTags')
            ->with($tag)
            ->willReturn(new EntityReader($select));

        $dataReader = $this->service->findByTag($tag);

        $sortDataReader = $dataReader->getSort();
        $this->assertEquals(
            ['id' => 'asc', 'title' => 'asc', 'public' => 'asc', 'updated_at' => 'asc', 'published_at' => 'asc'],
            $sortDataReader->getDefaultOrder(),
        );
        $this->assertEquals(['published_at' => 'desc'], $sortDataReader->getOrder());
        $this->assertEquals($selectResult, $dataReader->read());
    }

    public function testGetPostBySlug(): void
    {
        $slug = 'test-post';
        $post = $this->createMock(Post::class);

        $this->repository
            ->expects($this->once())
            ->method('findBySlug')
            ->with($slug)
            ->willReturn($post);

        $result = $this->service->getPostBySlug($slug);

        $this->assertSame($post, $result);
    }

    public function testGetPostBySlugNotFound(): void
    {
        $slug = 'test-post';
        $post = $this->createMock(Post::class);

        $this->repository
            ->expects($this->once())
            ->method('findBySlug')
            ->with($slug)
            ->willReturn(null);

        $result = $this->service->getPostBySlug($slug);

        $this->assertNull($result);
    }

    /**
     * @throws Exception
     */
    public function testGetPostById(): void
    {
        $postId = 1;
        $post = $this->createMock(Post::class);

        $this->repository
            ->expects($this->once())
            ->method('findById')
            ->with($postId)
            ->willReturn($post);

        $result = $this->service->getPost($postId);

        $this->assertSame($post, $result);
    }

    /**
     * @throws Exception
     */
    public function testGetPostByIdNotFound(): void
    {
        $postId = 1;
        $post = $this->createMock(Post::class);

        $this->repository
            ->expects($this->once())
            ->method('findById')
            ->with($postId)
            ->willReturn(null);

        $result = $this->service->getPost($postId);

        $this->assertNull($result);
    }

    /**
     * @throws Exception
     */
    public function testFullPostPage(): void
    {
        $slug = 'test-post';
        $post = $this->createMock(Post::class);

        $this->repository
            ->expects($this->once())
            ->method('fullPostBySlug')
            ->with($slug)
            ->willReturn($post);

        $result = $this->service->fullPostPage($slug);

        $this->assertSame($post, $result);
    }

    /**
     * @throws Exception
     */
    public function testFullPostPageNotFound(): void
    {
        $slug = 'test-post';
        $post = $this->createMock(Post::class);

        $this->repository
            ->expects($this->once())
            ->method('fullPostBySlug')
            ->with($slug)
            ->willReturn(null);

        $result = $this->service->fullPostPage($slug);

        $this->assertNull($result);
    }

    /**
     * @throws DateMalformedStringException
     */
    public function testGetMaxUpdatedAt(): void
    {
        $maxUpdatedAt = '2023-01-01 12:00:00';

        $this->repository
            ->expects($this->once())
            ->method('getMaxUpdatedAt')
            ->willReturn(new DateTimeImmutable($maxUpdatedAt));

        $result = $this->service->getMaxUpdatedAt();

        $this->assertEquals($maxUpdatedAt, $result->format('Y-m-d H:i:s'));
    }

    /**
     * @throws Exception
     */
    #[Override]
    protected function _before(): void
    {
        $this->select = $this->createMock(Select::class);
        $this->repository = $this->createMock(PostRepositoryInterface::class);
        $this->service = new ReadPostQueryService($this->repository);
    }

    private function createMockSelectForReader(array $orderByEquals, array $resultFetchAll): Select&MockObject
    {
        $this->select
            ->method('__call')
            ->willReturnCallback(
                function (string $method, array $arguments) use ($orderByEquals) {
                    if ($method === 'getDriver') {
                        $driverMock = $this->createMock(DriverInterface::class);
                        $driverMock
                            ->expects($this->once())
                            ->method('getType')
                            ->willReturn('SQLite');
                        return $driverMock;
                    }
                    if ($method === 'orderBy') {
                        $this->assertEquals('orderBy', $method);
                        $this->assertEquals(
                            $orderByEquals,
                            $arguments,
                        );
                    }
                    return $this->select;
                },
            );
        $this->select
            ->method('fetchAll')
            ->willReturnCallback(
                fn() => $resultFetchAll,
            );

        return $this->select;
    }
}
