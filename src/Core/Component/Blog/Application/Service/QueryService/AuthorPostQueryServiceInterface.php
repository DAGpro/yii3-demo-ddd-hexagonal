<?php

declare(strict_types=1);

namespace App\Core\Component\Blog\Application\Service\QueryService;

use App\Core\Component\Blog\Domain\Exception\BlogNotFoundException;
use App\Core\Component\Blog\Domain\Post;
use App\Core\Component\Blog\Domain\User\Author;
use Yiisoft\Data\Reader\DataReaderInterface;

interface AuthorPostQueryServiceInterface
{
    public function getAuthorPosts(Author $author): DataReaderInterface;

    public function getPostBySlug(string $slug): ?Post;
}
