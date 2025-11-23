<?php

declare(strict_types=1);

namespace App\Tests\Unit\Blog\Application\Service\AppService\CommandService;

use App\Blog\Application\Service\AppService\CommandService\ModeratePostService;
use App\Blog\Application\Service\CommandService\PostModerateDTO;
use App\Blog\Application\Service\QueryService\ModeratePostQueryServiceInterface;
use App\Blog\Domain\Exception\BlogNotFoundException;
use App\Blog\Domain\Port\PostRepositoryInterface;
use App\Blog\Domain\Port\TagRepositoryInterface;
use App\Blog\Domain\Post;
use App\Blog\Domain\Tag;
use App\Blog\Domain\User\Author;
use App\Tests\UnitTester;
use Codeception\Test\Unit;
use Exception;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(ModeratePostService::class)]
final class ModeratePostServiceTest extends Unit
{
    protected UnitTester $tester;

    private ModeratePostService $service;

    private ModeratePostQueryServiceInterface&MockObject $postQueryService;

    private PostRepositoryInterface&MockObject $postRepository;

    private TagRepositoryInterface&MockObject $tagRepository;

    private int $postId = 1;

    private string $postTitle = 'Test Post';

    private string $postContent = 'Test Content';

    private array $postTags = ['tag1', 'tag2'];

    private Post $post;

    /**
     * @throws BlogNotFoundException
     */
    public function testPublicPost(): void
    {
        $this->postQueryService
            ->expects($this->once())
            ->method('getPost')
            ->with($this->postId)
            ->willReturn($this->post);

        $this->postRepository
            ->expects($this->once())
            ->method('save')
            ->with(
                $this->callback(
                    /** @param array<Post> $posts */
                    function (array $posts) {
                        $this->assertIsArray($posts);
                        $this->assertCount(1, $posts);
                        $post = $posts[0];
                        $this->assertTrue($post->isPublic());
                        return true;
                    },
                ),
            );

        $this->service->public($this->postId);
    }

    /**
     * @throws BlogNotFoundException
     */
    public function testDraftPost(): void
    {
        $this->post->publish();

        $this->postQueryService
            ->expects($this->once())
            ->method('getPost')
            ->with($this->postId)
            ->willReturn($this->post);

        $this->postRepository
            ->expects($this->once())
            ->method('save')
            ->with(
                $this->callback(
                    function ($posts) {
                        $this->assertFalse($posts[0]->isPublic());
                        return true;
                    },
                ),
            );

        $this->service->draft($this->postId);
    }

    /**
     * @throws BlogNotFoundException
     */
    public function testModeratePostWithPublish(): void
    {
        $newTitle = 'Updated Title';
        $newContent = 'Updated Content';
        $newTags = ['new-tag1', 'new-tag2'];

        $postModerateDTO = new PostModerateDTO($newTitle, $newContent, true, $newTags);

        $this->postQueryService
            ->expects($this->once())
            ->method('getPost')
            ->with($this->postId)
            ->willReturn($this->post);

        $tagsMap = [];
        foreach ($newTags as $tagLabel) {
            $tagsMap[] = [$tagLabel, new Tag($tagLabel)];
        }

        $this->tagRepository
            ->expects($this->exactly(count($newTags)))
            ->method('getOrCreate')
            ->willReturnMap($tagsMap);

        $this->postRepository
            ->expects($this->once())
            ->method('save')
            ->with(
                $this->callback(
                    /** @param array<Post> $posts */
                    function (array $posts) use ($newTitle, $newContent) {
                        $post = $posts[0];
                        $this->assertEquals($newTitle, $post->getTitle());
                        $this->assertEquals($newContent, $post->getContent());
                        $this->assertTrue($post->isPublic());
                        return true;
                    },
                ),
            );

        $this->service->moderate($this->postId, $postModerateDTO);
    }

    /**
     * @throws BlogNotFoundException
     */
    public function testModeratePostWithDraft(): void
    {
        $postModerateDTO = new PostModerateDTO('Title', 'Content', false, []);

        $this->postQueryService
            ->expects($this->once())
            ->method('getPost')
            ->with($this->postId)
            ->willReturn($this->post);

        $this->postRepository
            ->expects($this->once())
            ->method('save')
            ->with(
                $this->callback(
                    function ($posts) {
                        $this->assertFalse($posts[0]->isPublic());
                        return true;
                    },
                ),
            );

        // Act
        $this->service->moderate($this->postId, $postModerateDTO);
    }

    /**
     * @throws BlogNotFoundException
     */
    public function testDeletePost(): void
    {
        $this->postQueryService
            ->expects($this->once())
            ->method('getPost')
            ->with($this->postId)
            ->willReturn($this->post);

        $this->postRepository
            ->expects($this->once())
            ->method('delete')
            ->with([$this->post]);

        $this->service->delete($this->postId);
    }

    public function testPostNotFound(): void
    {
        $this->postQueryService
            ->expects($this->once())
            ->method('getPost')
            ->with($this->postId)
            ->willReturn(null);

        $this->expectException(BlogNotFoundException::class);
        $this->expectExceptionMessage('This post does not exist!');

        $this->service->delete($this->postId);
    }

    public function testPublicThrowsExceptionWhenPostNotFound(): void
    {
        $this->postQueryService
            ->expects($this->once())
            ->method('getPost')
            ->with($this->postId)
            ->willReturn(null);

        $this->expectException(BlogNotFoundException::class);
        $this->expectExceptionMessage('This post does not exist!');

        $this->service->public($this->postId);
    }

    public function testDraftThrowsExceptionWhenPostNotFound(): void
    {
        $this->postQueryService
            ->expects($this->once())
            ->method('getPost')
            ->with($this->postId)
            ->willReturn(null);

        $this->expectException(BlogNotFoundException::class);
        $this->expectExceptionMessage('This post does not exist!');

        $this->service->draft($this->postId);
    }

    public function testModerateThrowsExceptionWhenPostNotFound(): void
    {
        $postModerateDTO = new PostModerateDTO('Title', 'Content', true, []);

        $this->postQueryService
            ->expects($this->once())
            ->method('getPost')
            ->with($this->postId)
            ->willReturn(null);

        $this->expectException(BlogNotFoundException::class);
        $this->expectExceptionMessage('This post does not exist!');

        $this->service->moderate($this->postId, $postModerateDTO);
    }

    /**
     * @throws Exception
     */
    #[Override]
    protected function _before(): void
    {
        $this->postQueryService = $this->createMock(ModeratePostQueryServiceInterface::class);
        $this->postRepository = $this->createMock(PostRepositoryInterface::class);
        $this->tagRepository = $this->createMock(TagRepositoryInterface::class);

        $this->service = new ModeratePostService(
            $this->postQueryService,
            $this->postRepository,
            $this->tagRepository,
        );

        $this->post = new Post($this->postTitle, $this->postContent, new Author(1, 'Test Author'));
    }
}
