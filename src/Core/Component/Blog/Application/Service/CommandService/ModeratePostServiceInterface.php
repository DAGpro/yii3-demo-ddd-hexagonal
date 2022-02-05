<?php

declare(strict_types=1);

namespace App\Core\Component\Blog\Application\Service\CommandService;


interface ModeratePostServiceInterface
{

    public function public(int $postId): void;

    public function draft(int $postId): void;

    public function moderate(int $postId, PostModerateDTO $postModerateDTO): void;

    public function delete(int $postId): void;
}
