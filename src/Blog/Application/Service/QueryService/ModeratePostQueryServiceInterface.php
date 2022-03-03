<?php

declare(strict_types=1);

namespace App\Blog\Application\Service\QueryService;

use App\Blog\Domain\Post;
use Yiisoft\Data\Reader\DataReaderInterface;

interface ModeratePostQueryServiceInterface
{
    /**
     * Get posts without filter with preloaded Users and Tags
     *
     * @psalm-return DataReaderInterface<int, Post>
     */
    public function findAllPreloaded(): DataReaderInterface;

    public function getPost(int $id): ?Post;
}
