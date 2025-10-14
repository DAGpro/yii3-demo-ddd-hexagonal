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
     * @return DataReaderInterface array with archive data (year, month, number of posts)
     */
    public function getFullArchive(): DataReaderInterface;

    /**
     * @param int $year year
     * @param int $month month (1-12)
     * @return DataReaderInterface List of posts for the specified month
     */
    public function getMonthlyArchive(int $year, int $month): DataReaderInterface;

    /**
     * @param int $year year
     * @return DataReaderInterface List of posts for the specified year
     */
    public function getYearlyArchive(int $year): DataReaderInterface;

    /**
     * @return DataReaderInterface<int, Post>
     */
    public function findAllWithPreloadedTags(): DataReaderInterface;

    /**
     * @return DataReaderInterface<int, Post>
     */
    public function findByTagWithPreloadedTags(Tag $tag): DataReaderInterface;

    /**
     * @return DataReaderInterface<int, Post>
     */
    public function findByAuthorNotDeletedPostWithPreloadedTags(Author $author): DataReaderInterface;

    /**
     * @return DataReaderInterface<int, Post>
     */
    public function findByAuthorWithPreloadedTags(Author $author): DataReaderInterface;

    public function findBySlugWithPreloadedTags(string $slug): ?Post;

    public function findBySlugWithPreloadedTagsAndComments(string $slug): ?Post;

    public function findBySlugNotDeletedPostWithPreloadedTags(string $slug): ?Post;

    /**
     * @return DataReaderInterface<int, Post>
     */
    public function findAllForModerationWithPreloadedTags(): DataReaderInterface;

    public function findByIdWithPreloadedTags(int $id): ?Post;

    public function findByIdForModeration(int $id): ?Post;

    public function select(): Select;

    public function getMaxUpdatedAt(): DateTimeImmutable;

    /**
     * @param iterable<Post> $posts
     */
    public function save(iterable $posts): void;

    /**
     * @param iterable<Post> $posts
     */
    public function delete(iterable $posts): void;
}
