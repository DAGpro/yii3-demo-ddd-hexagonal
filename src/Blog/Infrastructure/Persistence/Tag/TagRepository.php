<?php

declare(strict_types=1);

namespace App\Blog\Infrastructure\Persistence\Tag;

use App\Blog\Domain\Port\TagRepositoryInterface;
use App\Blog\Domain\Post;
use App\Blog\Domain\Tag;
use App\Blog\Infrastructure\Persistence\Post\PostRepository;
use App\Blog\Infrastructure\Persistence\Post\PostTag;
use Cycle\ORM\EntityManagerInterface;
use Cycle\ORM\ORMInterface;
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
        private readonly ORMInterface $orm,
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

    /**
     * For example, below are several ways to make queries
     * As a result, we should get a list of most used tags
     * All SQL-queries received on mysql database. SQL-queries may vary by driver
     */
    #[Override]
    public function getTagMentions(): DataReaderInterface
    {
        /**
         * Case would look like:
         *
         * SELECT `label`, count(*) `count`
         * FROM `tag` AS `tag`
         * INNER JOIN `post_tag` AS `tag_posts_pivot`
         * ON `tag_posts_pivot`.`tag_id` = `tag`.`id`
         * INNER JOIN `post` AS `tag_posts`
         * ON `tag_posts`.`id` = `tag_posts_pivot`.`post_id` AND `tag_posts`.`public` = TRUE
         * GROUP BY `tag_posts_pivot`.`tag_id`, `tag`.`label`
         * ORDER BY `count` DESC
         */
        $tagMentions = $this
            ->select()
            ->groupBy('posts.@.tag_id') // relation posts -> pivot (@) -> column
            ->groupBy('label')
            ->buildQuery()
            ->columns(['label', 'count(*) count']);

        return new EntityReader($tagMentions);
    }

    public function getTagMentionsV2(): DataReaderInterface
    {
        /** @var Repository $postTagRepo */
        $postTagRepo = $this->orm->getRepository(PostTag::class);
        /**
         * Case 2 would look like:
         *
         * SELECT `t`.`label`, count(*) `count`
         * FROM `post_tag` AS `postTag`
         * INNER JOIN `post` AS `p`
         * ON `p`.`id` = `postTag`.`post_id` AND `p`.`public` = TRUE
         * INNER JOIN `tag` AS `t`
         * ON `t`.`id` = `postTag`.`tag_id`
         * GROUP BY `t`.`label`, `tag_id`
         * ORDER BY `count` DESC
         */
        $tagMentions = $postTagRepo
            ->select()
            ->buildQuery()
            ->columns(['t.label', 'count(*) count'])
            ->innerJoin('post', 'p')
            ->on('p.id', 'postTag.post_id')
            ->onWhere(['p.public' => true])
            ->innerJoin('tag', 't')
            ->on('t.id', 'postTag.tag_id')
            ->groupBy('t.label, tag_id');

        return new EntityReader($tagMentions);
    }

    public function getTagMentionsV3(): DataReaderInterface
    {
        /**
         * Case 3 would look like:
         *
         * SELECT `label`, count(*) `count`
         * FROM `tag` AS `tag`
         * INNER JOIN `post_tag` AS `tag_posts_pivot`
         * ON `tag_posts_pivot`.`tag_id` = `tag`.`id`
         * INNER JOIN `post` AS `tag_posts`
         * ON `tag_posts`.`id` = `tag_posts_pivot`.`post_id` AND `tag_posts`.`public` = TRUE
         * GROUP BY `tag`.`label`, `tag_id`
         * ORDER BY `count` DESC
         */
        $tagMentions = $this
            ->select()
            ->with('posts')
            ->buildQuery()
            ->columns(['label', 'count(*) count'])
            ->groupBy('tag.label, tag_id');

        return new EntityReader($tagMentions);
    }

    public function getTagMentionsV4(): DataReaderInterface
    {
        /** @var PostRepository $postRepo */
        $postRepo = $this->orm->getRepository(Post::class);
        /**
         * Case 4 would look like:
         *
         * SELECT `label`, count(*) `count`
         * FROM `post` AS `post`
         * INNER JOIN `post_tag` AS `post_tags_pivot`
         * ON `post_tags_pivot`.`post_id` = `post`.`id`
         * INNER JOIN `tag` AS `post_tags`
         * ON `post_tags`.`id` = `post_tags_pivot`.`tag_id`
         * WHERE `post`.`public` = TRUE
         * GROUP BY `post_tags_pivot`.`tag_id`, `tag`.`label`
         */
        $tagMentions = $postRepo
            ->select()
            ->groupBy('tags.@.tag_id') // relation tags -> pivot (@) -> column
            ->groupBy('tags.label')
            ->buildQuery()
            ->columns(['label', 'count(*) count']);

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
