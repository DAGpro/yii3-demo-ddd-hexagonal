<?php

declare(strict_types=1);

namespace App\Blog\Application\Service\CommandService;

use App\Blog\Domain\User\Author;

interface AuthorPostServiceInterface
{
    public function create(PostCreateDTO $postCreateDTO, Author $author): void;

    public function edit(string $postSlug, PostChangeDTO $postChangeDTO): void;

    public function delete(string $postSlug);

}
