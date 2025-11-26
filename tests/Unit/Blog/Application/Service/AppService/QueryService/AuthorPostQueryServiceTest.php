<?php

declare(strict_types=1);

namespace App\Tests\Unit\Blog\Application\Service\AppService\QueryService;

use App\Blog\Domain\Port\PostRepositoryInterface;
use App\Blog\Domain\Post;
use App\Blog\Domain\User\Author;
use App\Blog\Slice\Post\Service\QueryService\AuthorPostQueryService;
use App\Tests\UnitTester;
use Codeception\Test\Unit;
use Cycle\Database\Driver\DriverInterface;
use Cycle\ORM\Select;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use Yiisoft\Data\Cycle\Reader\EntityReader;

#[CoversClass(AuthorPostQueryService::class)]
final class AuthorPostQueryServiceTest extends Unit
{
    protected UnitTester $tester;

    private AuthorPostQueryService $authorPostQueryService;

    private PostRepositoryInterface&MockObject $postRepository;

    private Select&MockObject $select;

    public function testGetAuthorPosts(): void
    {
        $authorId = 1;
        $author = new Author($authorId, 'Test Author');

        $query = $this->select;
        $query
            ->expects($this->exactly(3))
            ->method('__call')
            ->willReturnCallback(
                function (string $method, array $arguments) use ($query, $authorId) {
                    if ($method === 'getDriver') {
                        $driverMock = $this
                            ->createMock(DriverInterface::class);
                        $driverMock
                            ->expects($this->once())
                            ->method('getType')
                            ->willReturn('SQLite');
                        return $driverMock;
                    }

                    if ($method === 'orderBy') {
                        $this->assertEquals('orderBy', $method);
                        $this->assertEquals(
                            [
                                [
                                    'published_at' => 'DESC',
                                ],
                            ],
                            $arguments,
                        );
                    }

                    return $query;
                },
            );

        $selectResult = [
            new Post('title', 'content', new Author(1, 'Author')),
            new Post('title 2', 'content 2', new Author(1, 'Author')),
        ];

        $query
            ->expects($this->once())
            ->method('fetchAll')
            ->willReturn($selectResult);

        $this->postRepository
            ->expects($this->once())
            ->method('findByAuthorNotDeletedPostWithPreloadedTags')
            ->with($author)
            ->willReturn(new EntityReader($query));

        $dataReader = $this->authorPostQueryService->getAuthorPosts($author);

        $sortDataReader = $dataReader->getSort();
        $this->assertEquals(
            ['published_at' => 'asc',],
            $sortDataReader->getDefaultOrder(),
        );
        $this->assertEquals(['published_at' => 'desc'], $sortDataReader->getOrder());
        $this->assertEquals($selectResult, $dataReader->read());
    }

    public function testGetPostBySlugWhenPostExists(): void
    {
        $slug = 'test-post';
        $post = new Post('Test Post', 'Test content', new Author(1, 'Author'));

        $this->postRepository
            ->expects($this->once())
            ->method('findBySlugNotDeletedPostWithPreloadedTags')
            ->with($slug)
            ->willReturn($post);

        $result = $this->authorPostQueryService->getPostBySlug($slug);

        $this->assertSame($post, $result);
    }

    public function testGetPostBySlugWhenPostNotExists(): void
    {
        $slug = 'non-existent-post';

        $this->postRepository
            ->expects($this->once())
            ->method('findBySlugNotDeletedPostWithPreloadedTags')
            ->with($slug)
            ->willReturn(null);

        $result = $this->authorPostQueryService->getPostBySlug($slug);


        $this->assertNull($result);
    }

    /**
     * @return void
     * @throws Exception
     */
    #[Override]
    protected function _before(): void
    {
        $this->select = $this->createMock(Select::class);
        $this->postRepository = $this->createMock(PostRepositoryInterface::class);

        $this->authorPostQueryService = new AuthorPostQueryService(
            $this->postRepository,
        );
    }
}
