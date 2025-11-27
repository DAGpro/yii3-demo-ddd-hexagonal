<?php

declare(strict_types=1);

namespace App\Tests\Integration\Blog\Slice\Comment\Infrastructure\Repository;

use App\Blog\Domain\Comment;
use App\Blog\Domain\Port\CommentRepositoryInterface;
use App\Blog\Domain\Post;
use App\Blog\Domain\User\Author;
use App\Blog\Domain\User\Commentator;
use App\Tests\Integration\TestCase;
use App\Tests\UnitTester;
use DateTimeImmutable;
use Override;

class CommentRepositoryTest extends TestCase
{
    protected UnitTester $tester;

    private CommentRepositoryInterface $repository;

    public function testSaveAndFindComment(): void
    {
        $author = new Author(1, 'Test Author');
        $post = new Post('Test Post', 'Test Content', $author);
        $post->publish();

        $commentator = new Commentator(1, 'Test Commentator');
        $comment = new Comment('Test comment content', $post, $commentator);
        $comment->publish();

        $this->repository->save([$comment]);

        $foundComment = $this->repository->getComment($comment->getId());

        $this->assertNotNull($foundComment);
        $this->assertEquals('Test comment content', $foundComment->getContent());
        $this->assertTrue($foundComment->isPublic());
        $this->assertInstanceOf(DateTimeImmutable::class, $foundComment->getPublishedAt());
    }

    public function testDeleteComment(): void
    {
        $author = new Author(1, 'Test Author');
        $post = new Post('Test Post', 'Test Content', $author);
        $post->publish();

        $commentator = new Commentator(1, 'Test Commentator');
        $comment = new Comment('Comment to delete', $post, $commentator);
        $comment->publish();

        $this->repository->save([$comment]);
        $commentId = $comment->getId();

        $this->repository->delete([$comment]);

        $deletedComment = $this->repository->getPublicComment($commentId);

        $this->assertNull($deletedComment);
    }

    public function testGetPublicComment(): void
    {
        $author = new Author(1, 'Test Author');
        $post = new Post('Test Post', 'Test Content', $author);
        $post->publish();

        $commentator = new Commentator(1, 'Test Commentator');

        $publicComment = new Comment('Public comment', $post, $commentator);
        $publicComment->publish();

        $privateComment = new Comment('Private comment', $post, clone $commentator);

        $this->repository->save([$publicComment, $privateComment]);

        $foundPublic = $this->repository->getPublicComment($publicComment->getId());
        $this->assertNotNull($foundPublic);
        $this->assertEquals('Public comment', $foundPublic->getContent());

        $foundPrivate = $this->repository->getPublicComment($privateComment->getId());
        $this->assertNull($foundPrivate);
    }

    public function testFindAllNonDeleted(): void
    {
        $author = new Author(1, 'Test Author');
        $post = new Post('Test Post', 'Test Content', $author);
        $post->publish();

        $commentator = new Commentator(1, 'Test Commentator');

        $activeComment1 = new Comment('Active comment 1', $post, $commentator);
        $activeComment1->publish();

        $activeComment2 = new Comment('Active comment 2', $post, clone $commentator);
        $activeComment2->publish();

        $toDeleteComment = new Comment('To be deleted comment', $post, clone $commentator);
        $toDeleteComment->publish();

        $this->repository->save([$activeComment1, $activeComment2, $toDeleteComment]);

        $this->repository->delete([$toDeleteComment]);

        $nonDeletedComments = $this->repository->findAllNonDeleted();
        $comments = $nonDeletedComments->read();

        $this->assertCount(2, $comments);

        $contents = array_map(static fn(Comment $c): string => $c->getContent(), $comments);
        $this->assertContains('Active comment 1', $contents);
        $this->assertContains('Active comment 2', $contents);
        $this->assertNotContains('Deleted comment', $contents);
    }

    #[Override]
    protected function _before(): void
    {
        parent::_before();

        /** @var CommentRepositoryInterface $repository */
        $repository = self::$orm->getRepository(Comment::class);
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
