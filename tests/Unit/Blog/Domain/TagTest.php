<?php

declare(strict_types=1);

namespace App\Tests\Unit\Blog\Domain;

use App\Blog\Domain\Post;
use App\Blog\Domain\Tag;
use DateTimeImmutable;
use Error;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[CoversClass(Tag::class)]
final class TagTest extends TestCase
{
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
        // Новый тег (еще не сохранен в БД)
        $this->assertFalse($this->tag->hasId());

        // Эмулируем сохраненный тег
        $this->setPrivateProperty($this->tag, 'id', 1);

        $this->assertTrue($this->tag->hasId());
    }

    public function testUniqueIds(): void
    {
        $tag1 = new Tag('tag1');
        $tag2 = new Tag('tag2');

        // Проверяем, что у новых тегов нет ID
        $this->assertNull($tag1->getId());
        $this->assertNull($tag2->getId());

        // Эмулируем сохранение в БД с разными ID
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

    public function testLabelBoundaryValues(): void
    {
        // Тест с пустой строкой
        $tag = new Tag('');
        $this->assertSame('', $tag->getLabel());

        // Тест с очень длинной строкой (больше 191 символа)
        $longLabel = str_repeat('a', 200);
        $tag = new Tag($longLabel);
        $this->assertSame($longLabel, $tag->getLabel());

        // Тест с эмодзи и спецсимволами
        $specialLabel = 'Тег с эмодзи 😊 и #спецсимволами!';
        $tag = new Tag($specialLabel);
        $this->assertSame($specialLabel, $tag->getLabel());
    }

    public function testPostCollection(): void
    {
        // Создаем моки постов с разными ID
        $post1 = $this->createMock(Post::class);
        $post1->method('getId')->willReturn(1);

        $post2 = $this->createMock(Post::class);
        $post2->method('getId')->willReturn(2);

        // Добавляем первый пост
        $this->tag->addPost($post1);
        $this->assertCount(1, $this->tag->getPosts(), 'Тег должен содержать один пост');
        $this->assertContains($post1, $this->tag->getPosts());

        // Добавляем второй пост
        $this->tag->addPost($post2);
        $posts = $this->tag->getPosts();
        $this->assertCount(2, $posts, 'Тег должен содержать два поста');
        $this->assertContains($post1, $posts);
        $this->assertContains($post2, $posts);

        // Проверяем, что посты можно получить по индексу
        $this->assertSame($post1, $posts[0]);
        $this->assertSame($post2, $posts[1]);

        // Проверяем, что добавление того же поста добавляет его снова
        $initialCount = count($this->tag->getPosts());
        $this->tag->addPost($post1);
        $this->assertCount($initialCount + 1,
            $this->tag->getPosts(),
            'Добавление существующего поста добавляет его снова',
        );

        // Проверяем, что последний добавленный пост - это post1
        $posts = $this->tag->getPosts();
        $this->assertSame($post1, end($posts));
    }

    #[Override]
    protected function setUp(): void
    {
        $this->tag = new Tag($this->label);
    }

    /**
     * Устанавливает значение приватного свойства через рефлексию
     */
    private function setPrivateProperty(object $object, string $property, $value): void
    {
        $reflection = new ReflectionClass($object);
        $property = $reflection->getProperty($property);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }
}
