<?php

declare(strict_types=1);

namespace App\Tests\Integration\Blog\Infrastructure\Persistence\Post;

use App\Blog\Domain\Port\PostRepositoryInterface;
use App\Blog\Domain\Post;
use App\Blog\Domain\Tag;
use App\Blog\Domain\User\Author;
use App\Tests\Integration\TestCase;
use App\Tests\UnitTester;
use DateTimeImmutable;
use Override;

class PostRepositoryTest extends TestCase
{
    protected UnitTester $tester;

    private PostRepositoryInterface $repository;

    public function testSaveAndFindPost(): void
    {
        $author = new Author(1, 'Test Author');
        $post = new Post('Test Post', 'Test Content', $author);
        $post->publish();

        $this->repository->save([$post]);
        $postId = $post->getId();

        $foundPost = $this->repository->findById($postId);

        $this->assertNotNull($foundPost);
        $this->assertSame($post->getTitle(), $foundPost->getTitle());
        $this->assertSame($post->getContent(), $foundPost->getContent());
        $this->assertTrue($foundPost->isPublic());
        $this->assertInstanceOf(DateTimeImmutable::class, $foundPost->getPublishedAt());
    }

    public function testDeletePost(): void
    {
        $author = new Author(1, 'Test Author');
        $post = new Post('Test Post', 'Test Content', $author);
        $post->publish();

        $this->repository->save([$post]);
        $postId = $post->getId();

        $this->repository->delete([$post]);

        $deletedPost = $this->repository->findById($postId);
        $this->assertNull($deletedPost);
    }

    public function testGetFullArchive(): void
    {
        $author = new Author(1, 'Test Author');
        $author2 = new Author(1, 'Test Author');
        $author3 = new Author(1, 'Test Author');

        $post1 = new Post('Post 1', 'Content 1', $author);
        $post1->publish();
        $post1->setPublishedAt(new DateTimeImmutable('2023-01-15'));

        $post2 = new Post('Post 2', 'Content 2', $author2);
        $post2->publish();
        $post2->setPublishedAt(new DateTimeImmutable('2023-02-20'));

        $post3 = new Post('Post 3', 'Content 3', $author3);
        $post3->publish();
        $post3->setPublishedAt(new DateTimeImmutable('2024-01-10'));

        $this->repository->save([$post1, $post2, $post3]);

        $dataReader = $this->repository->getFullArchive();
        $archiveItems = $dataReader->read();

        $this->assertCount(3, $archiveItems);
        // Check sorting (first new ones)
        $this->assertEquals('2023', $archiveItems[0]['year']);
        $this->assertEquals('01', $archiveItems[0]['month']);
        $this->assertEquals('2023', $archiveItems[1]['year']);
        $this->assertEquals('02', $archiveItems[1]['month']);
        $this->assertEquals('2024', $archiveItems[2]['year']);
        $this->assertEquals('01', $archiveItems[2]['month']);
    }

    public function testGetMonthlyArchive(): void
    {
        $author = new Author(1, 'Test Author');

        $post1 = new Post('Post 1', 'Content 1', $author);
        $post1->publish();
        $post1->setPublishedAt(new DateTimeImmutable('2023-01-15'));

        $post2 = new Post('Post 2', 'Content 2', clone $author);
        $post2->publish();
        $post2->setPublishedAt(new DateTimeImmutable('2023-01-20'));

        // Пост за другой месяц
        $post3 = new Post('Post 3', 'Content 3', clone $author);
        $post3->publish();
        $post3->setPublishedAt(new DateTimeImmutable('2023-02-10'));

        $this->repository->save([$post1, $post2, $post3]);

        // We get an archive for January 2023
        $dataReader = $this->repository->getMonthlyArchive(2023, 1);
        $posts = $dataReader->read();

        $this->assertCount(2, $posts);
        $this->assertEquals('Post 1', $posts[0]->getTitle());
        $this->assertEquals('Post 2', $posts[1]->getTitle());
    }

    public function testGetYearlyArchive(): void
    {
        $author = new Author(1, 'Test Author');

        $post1 = new Post('Post 1', 'Content 1', $author);
        $post1->publish();
        $post1->setPublishedAt(new DateTimeImmutable('2023-01-15'));

        $post2 = new Post('Post 2', 'Content 2', clone $author);
        $post2->publish();
        $post2->setPublishedAt(new DateTimeImmutable('2023-06-20'));

        $post3 = new Post('Post 3', 'Content 3', clone $author);
        $post3->publish();
        $post3->setPublishedAt(new DateTimeImmutable('2024-01-10'));

        $this->repository->save([$post1, $post2, $post3]);

        $dataReader = $this->repository->getYearlyArchive(2023);
        $posts = $dataReader->read();

        $this->assertCount(2, $posts);
        $this->assertEquals('Post 1', $posts[0]->getTitle());
        $this->assertEquals('Post 2', $posts[1]->getTitle());
    }

    public function testFindAllWithTags(): void
    {
        $author = new Author(1, 'Test Author');

        $post1 = new Post('Test Post 1', 'Test Content 1', $author);
        $post2 = new Post('Test Post 2', 'Test Content 2', clone $author);

        $post1->publish();
        $post2->publish();

        $this->repository->save([$post1, $post2]);

        $dataReader = $this->repository->getAllWithPreloadedTags();
        $posts = $dataReader->read();

        $this->assertCount(2, $posts, 'Should find all posts with tags');
    }

    public function testFindByTag(): void
    {
        $author = new Author(1, 'Test Author');

        $tag1 = new Tag('tag1');
        $tag2 = new Tag('tag2');

        // Create posts with the author and tags
        $post1 = new Post('Test Post 1', 'Test Content 1', $author);
        $post2 = new Post('Test Post 2', 'Test Content 2', clone $author);

        $post1->publish();
        $post2->publish();

        $post1->addTag($tag1);
        $post2->addTag($tag2);

        $this->repository->save([$post1, $post2]);

        $dataReader = $this->repository->findByTagWithPreloadedTags($tag1);
        $posts = $dataReader->read();

        $this->assertCount(1, $posts, 'Should find one post with the specified tag');
        $this->assertSame($post1->getTitle(), $posts[0]->getTitle());
    }

    public function testFindBySlug(): void
    {
        $author = new Author(1, 'Test Author');
        $post = new Post('Test Post', 'Test Content', $author);
        $post->publish();

        $this->repository->save([$post]);
        $slug = $post->getSlug();

        $this->assertNotEmpty($slug);
        $foundPost = $this->repository->findBySlug($slug);
        $this->assertNotNull($foundPost, 'Post should be found by slug');
        $this->assertSame($post->getTitle(), $foundPost->getTitle());
    }

    public function testFindById(): void
    {
        $author = new Author(1, 'Test Author');
        $post = new Post('Test Post', 'Test Content', $author);
        $post->publish();

        $this->repository->save([$post]);

        $foundPost = $this->repository->findById($post->getId());

        $this->assertNotNull($foundPost, 'Post should be found by ID');
        $this->assertSame($post->getTitle(), $foundPost->getTitle());
    }

    public function testFindFullPostBySlug(): void
    {
        $author = new Author(1, 'Test Author');
        $post = new Post('Test Post', 'Test Content', $author);
        $post->publish();

        $this->repository->save([$post]);

        // Get the generated slug from the post
        $slug = $post->getSlug();
        $this->assertNotEmpty($slug);

        $foundPost = $this->repository->fullPostBySlug($slug);
        $this->assertNotNull($foundPost, 'Full post should be found by slug');
        $this->assertSame($post->getTitle(), $foundPost->getTitle());
    }

    public function testGetMaxUpdatedAt(): void
    {
        $author = new Author(1, 'Test Author');
        $post1 = new Post('Test Post 1', 'Test Content 1', $author);
        $post2 = new Post('Test Post 2', 'Test Content 2', clone $author);

        $post1->publish();
        $post2->publish();

        $this->repository->save([$post1, $post2]);

        $maxUpdatedAt = $this->repository->getMaxUpdatedAt();
        $this->assertInstanceOf(DateTimeImmutable::class, $maxUpdatedAt);
    }

    public function testFindAllForModerationWithPreloadedTags(): void
    {
        $author = new Author(1, 'Test Author');
        $post1 = new Post('Post 1', 'Content 1', $author);
        $post2 = new Post('Post 2', 'Content 2', clone $author);
        $post3 = new Post('Post 3', 'Content 3', clone $author);

        $post1->publish();
        $post2->publish();
        $post3->publish();

        $this->repository->save([$post1, $post2, $post3]);

        $dataReader = $this->repository->getAllForModerationWithPreloadedTags();
        $posts = $dataReader->read();

        $this->assertCount(3, $posts, 'Should find all non-deleted posts');
        $this->assertNotNull($posts[0]->getTags(), 'Tags should be preloaded');
    }

    public function testFindForModeration(): void
    {
        $author = new Author(1, 'Test Author');
        $post = new Post('Test Post', 'Test Content', $author);
        $post->publish();

        $this->repository->save([$post]);

        $postId = $post->getId();
        $foundPost = $this->repository->findByIdForModeration($postId);

        $this->assertNotNull($foundPost, 'Should find post by ID');
        $this->assertSame($post->getTitle(), $foundPost->getTitle());
    }

    public function testFindAuthorPostsWithPreloadedTags(): void
    {
        $author1 = new Author(1, 'Test Author 1');
        $author2 = new Author(2, 'Test Author 2');

        $post1 = new Post('Post 1', 'Content 1', $author1);
        $post2 = new Post('Post 2', 'Content 2', clone $author1);
        $post3 = new Post('Post 3', 'Content 3', $author2);

        $post1->publish();
        $post2->publish();
        $post3->publish();

        $this->repository->save([$post1, $post2, $post3]);

        $dataReader = $this->repository->findByAuthorNotDeletedPostWithPreloadedTags($author1);

        $posts = $dataReader->read();
        $this->assertCount(2, $posts, 'Should find all posts by author1');
        $this->assertSame($author1->getId(), $posts[0]->getAuthor()->getId());
        $this->assertSame($author1->getId(), $posts[1]->getAuthor()->getId());
        $this->assertNotNull($posts[0]->getTags(), 'Tags should be preloaded');
    }

    public function testFindPostBySlugWithPreloadedTags(): void
    {
        $author = new Author(1, 'Test Author');
        $post = new Post('Test Post', 'Test Content', $author);
        $post->publish();

        $this->repository->save([$post]);
        $slug = $post->getSlug();

        $foundPost = $this->repository->findBySlugNotDeletedPostWithPreloadedTags($slug);

        $this->assertNotNull($foundPost, 'Should find post by slug');
        $this->assertSame($post->getTitle(), $foundPost->getTitle());
        $this->assertNotNull($foundPost->getTags(), 'Tags should be preloaded');
    }

    #[Override]
    protected function _before(): void
    {
        parent::_before();

        if (self::$container === null) {
            $this->initializeContainer();
        }

        /** @var PostRepositoryInterface $repository */
        $repository = self::$orm->getRepository(Post::class);
        self::$database = $repository
            ->select()
            ->getBuilder()
            ->getLoader()
            ->getSource()
            ->getDatabase();

        $this->repository = $repository;

        $this->beginTransaction();
    }
}
