<?php

declare(strict_types=1);

namespace App\Blog\Slice\Post\Service\CommandService;

use App\Blog\Domain\Exception\BlogNotFoundException;

interface ModeratePostServiceInterface
{
    /**
     * @throws BlogNotFoundException
     */
    public function public(int $postId): void;

    public function draft(int $postId): void;

    /**
     * @throws BlogNotFoundException
     */
    public function moderate(int $postId, PostModerateDTO $postModerateDTO): void;

    public function delete(int $postId): void;
}
