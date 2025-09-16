<?php

declare(strict_types=1);

namespace App\Blog\Domain\Port;

use App\Blog\Domain\Post;
use App\Blog\Domain\Tag;
use App\Blog\Domain\User\Author;
use Cycle\ORM\Select;
use DateTimeImmutable;
use Yiisoft\Data\Reader\DataReaderInterface;

interface PostRepositoryInterface
{
    /**
     * Get a full archive of posts
     *
     * @return DataReaderInterface array with archive data (year, month, number of posts)
     */
    public function getFullArchive(): DataReaderInterface;

    /**
     * Get the archive of posts for the specified month
     *
     * @param int $year year
     * @param int $month month (1-12)
     * @return DataReaderInterface List of posts for the specified month
     */
    public function getMonthlyArchive(int $year, int $month): DataReaderInterface;

    /**
     * Get the archive of posts for the specified year
     *
     * @param int $year year
     * @return DataReaderInterface List of posts for the specified year
     */
    public function getYearlyArchive(int $year): DataReaderInterface;

    /**
     * @return DataReaderInterface<int, Post>
     */
    public function findAllWithTags(): DataReaderInterface;

    /**
     * @return DataReaderInterface<int, Post>
     */
    public function findByTag(Tag $tag): DataReaderInterface;

    /**
     * @return DataReaderInterface<int, Post>
     */
    public function findByAuthor(Author $author): DataReaderInterface;

    /**
     * Find post by slug
     */
    public function findBySlug(string $slug): ?Post;

    public function findById(int $id): ?Post;

    public function findFullPostBySlugWithPreloadedTagsAndComments(string $slug): ?Post;

    public function getMaxUpdatedAt(): DateTimeImmutable;

    /**
     * @return DataReaderInterface<int, Post>
     */
    public function findAllForModerationWithPreloadedTags(): DataReaderInterface;

    public function findForModeration(int $id): ?Post;

    /**
     * @return DataReaderInterface<int, Post>
     */
    public function findAuthorPostsWithPreloadedTags(Author $author): DataReaderInterface;

    public function findPostBySlugWithPreloadedTags(string $slug): ?Post;

    public function select(): Select;

    /**
     * @param iterable<Post> $posts
     */
    public function save(iterable $posts): void;

    /**
     * @param iterable<Post> $posts
     */
    public function delete(iterable $posts): void;
}
