<?php

declare(strict_types=1);

namespace App\Blog\Slice\Tag\Repository;

use App\Blog\Domain\Port\TagRepositoryInterface;
use App\Blog\Domain\Tag;
use Cycle\ORM\EntityManagerInterface;
use Cycle\ORM\Select;
use Cycle\ORM\Select\Repository;
use Override;
use Yiisoft\Data\Cycle\Reader\EntityReader;
use Yiisoft\Data\Reader\DataReaderInterface;

/**
 * @extends Repository<Tag>
 */
final class TagRepository extends Repository implements TagRepositoryInterface
{
    /**
     * @param Select<Tag> $select
     */
    public function __construct(
        protected Select $select,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct($select);
    }

    #[Override]
    public function findAllPreloaded(): DataReaderInterface
    {
        return new EntityReader($this->select());
    }

    #[Override]
    public function getOrCreate(string $label): Tag
    {
        $tag = $this->findByLabel($label);
        return $tag ?? new Tag($label);
    }

    #[Override]
    public function findByLabel(string $label): ?Tag
    {
        /** @var Tag|null $tag */
        $tag = $this
            ->select()
            ->where(['label' => $label])
            ->fetchOne();

        return $tag;
    }

    #[Override]
    public function getTag(int $tagId): ?Tag
    {
        /** @var Tag|null $tag */
        $tag = $this
            ->select()
            ->where(['id' => $tagId])
            ->fetchOne();

        return $tag;
    }

    #[Override]
    public function getTagMentions(): DataReaderInterface
    {
        $tagMentions = $this
            ->select()
            ->with('posts')
            ->buildQuery()
            ->columns(['label', 'count(*) count'])
            ->groupBy('tag.label, tag_id');

        return new EntityReader($tagMentions);
    }

    #[Override]
    /**
     * @param iterable<Tag> $tags
     */
    public function save(iterable $tags): void
    {
        if ($tags === []) {
            return;
        }

        foreach ($tags as $entity) {
            if ($entity instanceof Tag) {
                $this->entityManager->persist($entity);
            }
        }
        $this->entityManager->run();
    }

    #[Override]
    /**
     * @param iterable<Tag> $tags
     */
    public function delete(iterable $tags): void
    {
        if ($tags === []) {
            return;
        }

        foreach ($tags as $entity) {
            if ($entity instanceof Tag) {
                $this->entityManager->delete($entity);
            }
        }
        $this->entityManager->run();
    }
}
