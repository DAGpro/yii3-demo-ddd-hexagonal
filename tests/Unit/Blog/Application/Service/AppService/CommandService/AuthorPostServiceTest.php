<?php

declare(strict_types=1);

namespace App\Tests\Unit\Blog\Application\Service\AppService\CommandService;

use App\Blog\Application\Service\AppService\CommandService\AuthorPostService;
use App\Blog\Application\Service\CommandService\PostChangeDTO;
use App\Blog\Application\Service\CommandService\PostCreateDTO;
use App\Blog\Application\Service\QueryService\AuthorPostQueryServiceInterface;
use App\Blog\Domain\Exception\BlogAccessDeniedException;
use App\Blog\Domain\Exception\BlogNotFoundException;
use App\Blog\Domain\Port\PostRepositoryInterface;
use App\Blog\Domain\Port\TagRepositoryInterface;
use App\Blog\Domain\Post;
use App\Blog\Domain\Tag;
use App\Blog\Domain\User\Author;
use App\Tests\UnitTester;
use Codeception\Test\Unit;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(AuthorPostService::class)]
final class AuthorPostServiceTest extends Unit
{
    protected UnitTester $tester;

    private AuthorPostService $service;

    private MockObject&PostRepositoryInterface $postRepository;

    private MockObject&AuthorPostQueryServiceInterface $postQueryService;

    private MockObject&TagRepositoryInterface $tagRepository;

    private Author $author;
    private string $postTitle = 'Test Post';
    private string $postContent = 'Test Content';

    /** @var string[] */
    private array $postTags = ['tag1', 'tag2'];

    /**
     * @throws BlogNotFoundException
     */
    public function testCreatePost(): void
    {
        $postCreateDTO = new PostCreateDTO($this->postTitle, $this->postContent, $this->postTags);

        $tags = [];
        foreach ($this->postTags as $tagLabel) {
            $tags[] = [$tagLabel, new Tag($tagLabel)];
        }

        $this->tagRepository
            ->expects($this->exactly(2))
            ->method('getOrCreate')
            ->willReturnMap($tags);

        $this->postRepository
            ->expects($this->once())
            ->method('save')
            ->with(
                $this->callback(
                    /** @param array<array-key, mixed> $posts */
                    function (array $posts): bool {
                        $this->assertCount(1, $posts);
                        $post = $posts[0];
                        $this->assertInstanceOf(Post::class, $post);
                        $this->assertEquals($this->postTitle, $post->getTitle());
                        $this->assertEquals($this->postContent, $post->getContent());
                        $this->assertEquals(
                            array_values($this->postTags),
                            array_map(static fn(Tag $tag): string => $tag->getLabel(), $posts[0]->getTags()),
                        );
                        return true;
                    },
                ),
            );

        $this->service->create($postCreateDTO, $this->author);
    }

    /**
     * @throws BlogNotFoundException
     * @throws BlogAccessDeniedException
     */
    public function testEditPostSuccess(): void
    {
        $postSlug = 'test-post';
        $newTitle = 'Updated Title';
        $newContent = 'Updated Content';
        $newTags = ['new-tag1', 'new-tag2'];

        $post = new Post('Old Title', 'Old Content', $this->author);
        $postChangeDTO = new PostChangeDTO($newTitle, $newContent, $newTags);

        $this->postQueryService
            ->expects($this->once())
            ->method('getPostBySlug')
            ->with($postSlug)
            ->willReturn($post);

        $tagsMap = [];
        $tags = [];
        foreach ($newTags as $tagLabel) {
            $tag = new Tag($tagLabel);
            $tagsMap[] = [$tagLabel, $tag];
            $tags[] = $tag;
        }

        $this->tagRepository
            ->expects($this->exactly(2))
            ->method('getOrCreate')
            ->willReturnMap($tagsMap);

        $this->postRepository
            ->expects($this->once())
            ->method('save')
            ->with(
                $this->callback(
                    /** @param iterable<Post> $posts */
                    function (array $posts) use ($newTitle, $newContent, $tags) {
                        $this->assertIsArray($posts);
                        $this->assertCount(1, $posts);
                        $updatedPost = $posts[0];
                        $this->assertEquals($newTitle, $updatedPost->getTitle());
                        $this->assertEquals($newContent, $updatedPost->getContent());
                        $this->assertEquals($tags, $updatedPost->getTags());
                        return true;
                    },
                ),
            );

        $this->service->edit($postSlug, $postChangeDTO, $this->author);
    }

    /**
     * @throws BlogAccessDeniedException
     */
    public function testEditPostNotFound(): void
    {
        $postSlug = 'non-existent-post';
        $postChangeDTO = new PostChangeDTO('Title', 'Content', []);

        $this->postQueryService
            ->expects($this->once())
            ->method('getPostBySlug')
            ->with($postSlug)
            ->willReturn(null);

        $this->expectException(BlogNotFoundException::class);
        $this->expectExceptionMessage('This post does not exist!');

        $this->service->edit($postSlug, $postChangeDTO, $this->author);
    }

    /**
     * @throws BlogNotFoundException
     */
    public function testEditPostAccessDenied(): void
    {
        $postSlug = 'test-post';
        $otherAuthor = new Author(2, 'Other Author');
        $post = new Post('Title', 'Content', $otherAuthor);
        $postChangeDTO = new PostChangeDTO('Title', 'Content', []);

        $this->postQueryService
            ->expects($this->once())
            ->method('getPostBySlug')
            ->with($postSlug)
            ->willReturn($post);

        $this->expectException(BlogAccessDeniedException::class);
        $this->expectExceptionMessage('You are not the author of this post!');

        $this->service->edit($postSlug, $postChangeDTO, $this->author);
    }

    /**
     * @throws BlogNotFoundException
     * @throws BlogAccessDeniedException
     */
    public function testDeletePostSuccess(): void
    {
        $postSlug = 'test-post';
        $post = new Post('Title', 'Content', $this->author);

        $this->postQueryService
            ->expects($this->once())
            ->method('getPostBySlug')
            ->with($postSlug)
            ->willReturn($post);

        $this->postRepository
            ->expects($this->once())
            ->method('delete')
            ->with([$post]);

        $this->service->delete($postSlug, $this->author);
    }

    /**
     * @throws BlogAccessDeniedException
     */
    public function testDeletePostNotFound(): void
    {
        $postSlug = 'non-existent-post';

        $this->postQueryService
            ->expects($this->once())
            ->method('getPostBySlug')
            ->with($postSlug)
            ->willReturn(null);

        $this->expectException(BlogNotFoundException::class);
        $this->expectExceptionMessage('This post does not exist!');

        $this->service->delete($postSlug, $this->author);
    }

    public function testDeletePostAccessDenied(): void
    {
        $postSlug = 'test-post';
        $otherAuthor = new Author(2, 'Other Author');
        $post = new Post('Title', 'Content', $otherAuthor);

        $this->postQueryService
            ->expects($this->once())
            ->method('getPostBySlug')
            ->with($postSlug)
            ->willReturn($post);

        $this->expectException(BlogAccessDeniedException::class);
        $this->expectExceptionMessage('You are not the author of this post!');

        $this->service->delete($postSlug, $this->author);
    }

    /**
     * @throws Exception
     */
    protected function _before(): void
    {
        $this->postRepository = $this->makeEmpty(PostRepositoryInterface::class);
        $this->postQueryService = $this->makeEmpty(AuthorPostQueryServiceInterface::class);
        $this->tagRepository = $this->makeEmpty(TagRepositoryInterface::class);

        $this->service = new AuthorPostService(
            $this->postRepository,
            $this->postQueryService,
            $this->tagRepository,
        );

        $this->author = new Author(1, 'Test Author');
    }
}
