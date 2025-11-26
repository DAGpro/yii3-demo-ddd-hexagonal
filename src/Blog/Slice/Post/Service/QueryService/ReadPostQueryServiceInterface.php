<?php

declare(strict_types=1);

namespace App\Blog\Slice\Post\Service\QueryService;

use App\Blog\Domain\Post;
use App\Blog\Domain\Tag;
use DateTimeImmutable;
use Yiisoft\Data\Reader\DataReaderInterface;

interface ReadPostQueryServiceInterface
{
    /**
     * Get posts without filter with preloaded Users and Tags
     *
     * @psalm-return DataReaderInterface<int, Post>
     */
    public function findAllPreloaded(): DataReaderInterface;

    /**
     * @param Tag $tag
     * @return DataReaderInterface
     * @psalm-return DataReaderInterface<int, Post>
     */
    public function findByTag(Tag $tag): DataReaderInterface;

    public function getPostBySlug(string $slug): ?Post;

    public function getPost(int $id): ?Post;

    public function fullPostPage(string $slug): ?Post;

    public function getMaxUpdatedAt(): DateTimeImmutable;
}
