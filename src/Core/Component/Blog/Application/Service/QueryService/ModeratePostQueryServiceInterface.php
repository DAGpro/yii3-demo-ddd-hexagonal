<?php

declare(strict_types=1);

namespace App\Core\Component\Blog\Application\Service\QueryService;

use App\Core\Component\Blog\Domain\Post;
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
