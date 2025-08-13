<?php

declare(strict_types=1);

namespace App\Tests\Unit\Blog\Application\Service\AppService\QueryService;

use App\Blog\Application\Service\AppService\QueryService\AuthorPostQueryService;
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

#[CoversClass(AuthorPostQueryService::class)]
final class AuthorPostQueryServiceTest extends TestCase
{
    private AuthorPostQueryService $service;

    private PostRepositoryInterface $postRepository;

    private Select&MockObject $select;
    private Select\QueryBuilder&MockObject $queryBuilder;

    public function testGetAuthorPosts(): void
    {
        $authorId = 1;
        $author = new Author($authorId, 'Test Author');

        $query = $this->select;
        $query
            ->expects($this->once())
            ->method('load')
            ->with(['tags'])
            ->willReturn($query);

        $query
            ->expects($this->exactly(3))
            ->method('__call')
            ->willReturnCallback(
                function ($method, $arguments) use ($query, $authorId) {
                    if ($method === 'where' && $arguments[0] === 'author_id' && $arguments[1] === $authorId) {
                        return $query;
                    }

                    if ($method === 'getDriver') {
                        $driverMock = $this
                            ->createMock(DriverInterface::class);
                        $driverMock
                            ->expects($this->once())
                            ->method('getType')
                            ->willReturn('MySQL');
                        return $driverMock;
                    }

                    return $query;
                },
            );
        $result = $this->service->getAuthorPosts($author);

        $this->assertInstanceOf(DataReaderInterface::class, $result);
    }

    public function testGetPostBySlugWhenPostExists(): void
    {
        $slug = 'test-post';
        $post = new Post('Test Post', 'Test content', new Author(1, 'Author'));

        $query = $this->select;
        $query
            ->expects($this->once())
            ->method('load')
            ->with(['tags'])
            ->willReturn($query);

        $query
            ->expects($this->exactly(2))
            ->method('__call')
            ->willReturnCallback(
                function ($method, $arguments) use ($query, $slug) {
                    if ($method === 'andWhere' && $arguments[0] === 'slug' && $arguments[1] === '=' && $arguments[2] === $slug) {
                        return $query;
                    }

                    if ($method === 'andWhere' && $arguments[0] === 'deleted_at' && $arguments[1] === '=' && $arguments[2] === null) {
                        return $query;
                    }
                    return $query;
                },
            );

        $query
            ->expects($this->once())
            ->method('fetchOne')
            ->willReturn($post);

        $result = $this->service->getPostBySlug($slug);

        $this->assertSame($post, $result);
    }

    public function testGetPostBySlugWhenPostNotExists(): void
    {
        $slug = 'non-existent-post';

        $query = $this->select;
        $query
            ->expects($this->once())
            ->method('load')
            ->with(['tags'])
            ->willReturn($query);

        $query
            ->expects($this->exactly(2))
            ->method('__call')
            ->willReturnCallback(
                function ($method, $arguments) use ($query, $slug) {
                    if ($method === 'andWhere' && $arguments[0] === 'slug' && $arguments[1] === '=' && $arguments[2] === $slug) {
                        return $query;
                    }

                    if ($method === 'andWhere' && $arguments[0] === 'deleted_at' && $arguments[1] === '=' && $arguments[2] === null) {
                        return $query;
                    }
                    return $query;
                },
            );

        $query
            ->expects($this->once())
            ->method('fetchOne')
            ->willReturn(null);

        $result = $this->service->getPostBySlug($slug);


        $this->assertNull($result);
    }

    /**
     * @return void
     * @throws Exception
     */
    #[Override]
    protected function setUp(): void
    {
        $this->postRepository = new PostRepository(
            $this->select = $this->createMock(Select::class),
            $this->createMock(EntityManagerInterface::class),
        );


        $this->service = new AuthorPostQueryService($this->postRepository);
    }
}
