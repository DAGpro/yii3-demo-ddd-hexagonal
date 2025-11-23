<?php

declare(strict_types=1);

namespace App\Tests\Unit\Blog\Application\Service\AppService\QueryService;

use App\Blog\Application\Service\AppService\QueryService\ModeratePostQueryService;
use App\Blog\Domain\Port\PostRepositoryInterface;
use App\Blog\Domain\Post;
use App\Blog\Domain\User\Author;
use App\Tests\UnitTester;
use Codeception\Test\Unit;
use Cycle\Database\Driver\DriverInterface;
use Cycle\ORM\Select;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use Yiisoft\Data\Cycle\Reader\EntityReader;

#[CoversClass(ModeratePostQueryService::class)]
final class ModeratePostQueryServiceTest extends Unit
{
    protected UnitTester $tester;

    private ModeratePostQueryService $service;

    private PostRepositoryInterface&MockObject $postRepository;

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
                            ->willReturn('SQLite');
                        return $driverMock;
                    }

                    if ($method === 'orderBy') {
                        $this->assertEquals('orderBy', $method);
                        $this->assertEquals(
                            [
                                [
                                    'id' => 'ASC',
                                    'title' => 'ASC',
                                    'public' => 'ASC',
                                    'author_name' => 'ASC',
                                ],
                            ],
                            $arguments,
                        );
                    }

                    return $query;
                },
            );

        $author = $this->createMock(Author::class);
        $selectResult = [
            new Post('title', 'content', $author),
            new Post('title 2', 'content 2', $author),
        ];

        $query
            ->expects($this->once())
            ->method('fetchAll')
            ->willReturn(
                $selectResult,
            );

        $this->postRepository
            ->expects($this->once())
            ->method('getAllForModerationWithPreloadedTags')
            ->willReturn(new EntityReader($query));

        $dataReader = $this->service->findAllPreloaded();

        $sortDataReader = $dataReader->getSort();
        $this->assertEquals(
            ['id' => 'asc', 'title' => 'asc', 'public' => 'asc', 'author_name' => 'asc'],
            $sortDataReader->getDefaultOrder(),
        );
        $this->assertEquals(['published_at' => 'desc'], $sortDataReader->getOrder());
        $this->assertEquals($selectResult, $dataReader->read());
    }

    public function testGetPostWhenPostExists(): void
    {
        $postId = 1;
        $post = new Post('Test Post', 'Test content', new Author(1, 'Test Author'));

        $this->postRepository
            ->expects($this->once())
            ->method('findByIdForModeration')
            ->with($postId)
            ->willReturn($post);

        $result = $this->service->getPost($postId);

        $this->assertSame($post, $result);
    }

    public function testGetPostWhenPostNotExists(): void
    {
        $postId = 999;

        $this->postRepository
            ->expects($this->once())
            ->method('findByIdForModeration')
            ->with($postId)
            ->willReturn(null);

        $result = $this->service->getPost($postId);

        $this->assertNull($result);
    }

    /**
     * @throws Exception
     */
    #[Override]
    protected function _before(): void
    {
        $this->select = $this->createMock(Select::class);
        $this->postRepository = $this->createMock(PostRepositoryInterface::class);

        $this->service = new ModeratePostQueryService($this->postRepository);
    }
}
