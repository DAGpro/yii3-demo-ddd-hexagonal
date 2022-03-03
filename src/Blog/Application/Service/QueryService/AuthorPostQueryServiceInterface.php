<?php

declare(strict_types=1);

namespace App\Blog\Application\Service\QueryService;

use App\Blog\Domain\Post;
use App\Blog\Domain\User\Author;
use Yiisoft\Data\Reader\DataReaderInterface;

interface AuthorPostQueryServiceInterface
{
    public function getAuthorPosts(Author $author): DataReaderInterface;

    public function getPostBySlug(string $slug): ?Post;
}
