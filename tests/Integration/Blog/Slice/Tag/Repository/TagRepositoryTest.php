<?php

declare(strict_types=1);

namespace App\Tests\Integration\Blog\Slice\Tag\Repository;

use App\Blog\Domain\Port\TagRepositoryInterface;
use App\Blog\Domain\Post;
use App\Blog\Domain\Tag;
use App\Blog\Domain\User\Author;
use App\Tests\Integration\TestCase;
use App\Tests\UnitTester;
use Override;
use Yiisoft\Data\Reader\DataReaderInterface;

class TagRepositoryTest extends TestCase
{
    protected UnitTester $tester;

    private TagRepositoryInterface $repository;

    public function testFindAllPreloaded(): void
    {
        $tag1 = new Tag('Tag 1');
        $tag2 = new Tag('Tag 2');
        $this->repository->save([$tag1, $tag2]);

        $result = $this->repository->findAllPreloaded();

        /** @var DataReaderInterface<int, Tag> $result */
        $tags = $result->read();
        $this->assertCount(2, $tags);

        foreach ($tags as $index => $tag) {
            $index++;
            $this->assertEquals('Tag ' . $index, $tag->getLabel(), 'Unexpected tag label');
        }
    }

    public function testGetOrCreate(): void
    {
        $tag = $this->repository->getOrCreate('New Tag');
        $this->assertEquals('New Tag', $tag->getLabel());

        $sameTag = $this->repository->getOrCreate('New Tag');
        $this->assertEquals($tag->getId(), $sameTag->getId());
    }

    public function testFindByLabel(): void
    {
        $tag = new Tag('Test Tag');
        $this->repository->save([$tag]);

        $foundTag = $this->repository->findByLabel('Test Tag');
        $this->assertNotNull($foundTag);
        $this->assertEquals('Test Tag', $foundTag->getLabel());

        $notFoundTag = $this->repository->findByLabel('Nonexistent Tag');
        $this->assertNull($notFoundTag);
    }

    public function testGetTag(): void
    {
        $tag = new Tag('Test Tag');
        $this->repository->save([$tag]);
        $tagId = $tag->getId();

        $foundTag = $this->repository->getTag($tagId);
        $this->assertNotNull($foundTag);
        $this->assertEquals('Test Tag', $foundTag->getLabel());

        $notFoundTag = $this->repository->getTag(9999);
        $this->assertNull($notFoundTag);
    }

    public function testGetTagMentions(): void
    {
        $author = new Author(1, 'John Doe');
        $tag1 = new Tag('PHP');
        $tag2 = new Tag('Yii');
        $this->repository->save([$tag1, $tag2]);

        $post1 = new Post('Post 1', 'Content 1', $author);
        $post1->publish();
        $post1->addTag($tag1);
        $post1->addTag($tag2);

        $post2 = new Post('Post 2', 'Content 2', clone $author);
        $post2->publish();
        $post2->addTag($tag1);

        self::$orm
            ->getRepository(Post::class)
            ->save([$post1, $post2]);

        $dataReader = $this->repository->getTagMentions();
        $results = $dataReader->read();

        $this->assertCount(2, $results);

        $mentionsCount = [];
        foreach ($results as $row) {
            $mentionsCount[$row['label']] = $row['count'];
        }

        $this->assertEquals(2, $mentionsCount['PHP']);
        $this->assertEquals(1, $mentionsCount['Yii']);
    }

    public function testSaveAndDelete(): void
    {
        $tag1 = new Tag('Tag 1');
        $tag2 = new Tag('Tag 2');

        $this->repository->save([$tag1, $tag2]);

        $foundTag1 = $this->repository->findByLabel('Tag 1');
        $foundTag2 = $this->repository->findByLabel('Tag 2');

        $this->assertSame($tag1->getLabel(), $foundTag1->getLabel());
        $this->assertSame($tag2->getLabel(), $foundTag2->getLabel());

        $this->repository->delete([$tag1, $tag2]);

        $this->assertNull($this->repository->findByLabel('Tag 1'));
        $this->assertNull($this->repository->findByLabel('Tag 2'));
    }

    #[Override]
    protected function _before(): void
    {
        parent::_before();

        if (self::$container === null) {
            $this->initializeContainer();
        }

        /** @var TagRepositoryInterface $repository */
        $repository = self::$orm->getRepository(Tag::class);
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
