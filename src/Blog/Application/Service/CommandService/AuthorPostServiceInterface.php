<?php

declare(strict_types=1);

namespace App\Blog\Application\Service\CommandService;

use App\Blog\Domain\Exception\BlogAccessDeniedException;
use App\Blog\Domain\Exception\BlogNotFoundException;
use App\Blog\Domain\User\Author;

interface AuthorPostServiceInterface
{
    /**
     * @throws BlogNotFoundException
     */
    public function create(
        PostCreateDTO $postCreateDTO,
        Author $author,
    ): void;

    /**
     * @throws BlogNotFoundException
     * @throws BlogAccessDeniedException
     */
    public function edit(
        string $postSlug,
        PostChangeDTO $postChangeDTO,
        Author $author,
    ): void;

    /**
     * @throws BlogNotFoundException
     * @throws BlogAccessDeniedException
     * */
    public function delete(string $postSlug, Author $author): void;

}
