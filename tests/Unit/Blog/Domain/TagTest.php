<?php

declare(strict_types=1);

namespace App\Tests\Unit\Blog\Domain;

use App\Blog\Domain\Post;
use App\Blog\Domain\Tag;
use App\Tests\UnitTester;
use Codeception\Test\Unit;
use DateTimeImmutable;
use Error;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use ReflectionClass;

#[CoversClass(Tag::class)]
final class TagTest extends Unit
{
    protected UnitTester $tester;

    private Tag $tag;

    private string $label = 'test-tag';

    public function testCreate(): void
    {
        $this->assertNull($this->tag->getId());
        $this->assertSame($this->label, $this->tag->getLabel());
        $this->assertEmpty($this->tag->getPosts());
        $this->assertInstanceOf(DateTimeImmutable::class, $this->tag->getCreatedAt());
        $this->assertFalse($this->tag->hasId());
    }

    public function testChangeLabel(): void
    {
        $newLabel = 'updated-tag';
        $this->tag->change($newLabel);

        $this->assertSame($newLabel, $this->tag->getLabel());
    }

    /**
     * @throws Exception
     */
    public function testAddAndGetPosts(): void
    {
        $post1 = $this->createMock(Post::class);
        $post2 = $this->createMock(Post::class);

        $this->tag->addPost($post1);
        $this->tag->addPost($post2);

        $posts = $this->tag->getPosts();

        $this->assertCount(2, $posts);
        $this->assertContains($post1, $posts);
        $this->assertContains($post2, $posts);
    }

    public function testHasId(): void
    {
        // New tag (not yet saved in the database)
        $this->assertFalse($this->tag->hasId());

        // emulate the preserved tag
        $this->setPrivateProperty($this->tag, 'id', 1);

        $this->assertTrue($this->tag->hasId());
    }

    public function testUniqueIds(): void
    {
        $tag1 = new Tag('tag1');
        $tag2 = new Tag('tag2');

        // Check that new tags do not have ID
        $this->assertNull($tag1->getId());
        $this->assertNull($tag2->getId());

        // emulate saving to a database
        $this->setPrivateProperty($tag1, 'id', 1);
        $this->setPrivateProperty($tag2, 'id', 2);

        $this->assertNotSame($tag1->getId(), $tag2->getId());
    }

    public function testCreatedAtImmutable(): void
    {
        $initialCreatedAt = $this->tag->getCreatedAt();

        $newDate = new DateTimeImmutable('2023-01-01');

        $this->expectException(Error::class);
        $this->setPrivateProperty($this->tag, 'created_at', $newDate);
    }

    public function testPostCollection(): void
    {
        $post1 = $this->createMock(Post::class);
        $post1->method('getId')->willReturn(1);

        $post2 = $this->createMock(Post::class);
        $post2->method('getId')->willReturn(2);

        $this->tag->addPost($post1);
        $this->assertCount(1, $this->tag->getPosts(), 'The tag should contain one post');
        $this->assertContains($post1, $this->tag->getPosts());

        $this->tag->addPost($post2);
        $posts = $this->tag->getPosts();
        $this->assertCount(2, $posts, 'The tag must contain two posts');
        $this->assertContains($post1, $posts);
        $this->assertContains($post2, $posts);

        $this->assertSame($post1, $posts[0]);
        $this->assertSame($post2, $posts[1]);

        // Check that adding the same post adds it again
        $initialCount = count($this->tag->getPosts());
        $this->tag->addPost($post1);
        $this->assertCount(
            $initialCount + 1,
            $this->tag->getPosts(),
            'Добавление существующего поста добавляет его снова',
        );

        // Check that the last added post is post1
        $posts = $this->tag->getPosts();
        $this->assertSame($post1, end($posts));
    }

    #[Override]
    protected function _before(): void
    {
        $this->tag = new Tag($this->label);
    }

    /**
     * Sets the value of a private property through reflection
     */
    private function setPrivateProperty(object $object, string $property, $value): void
    {
        $reflection = new ReflectionClass($object);
        $property = $reflection->getProperty($property);
        $property->setValue($object, $value);
    }
}
