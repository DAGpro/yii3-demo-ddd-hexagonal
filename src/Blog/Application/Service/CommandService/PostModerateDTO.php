<?php

declare(strict_types=1);

namespace App\Blog\Application\Service\CommandService;

final class PostModerateDTO
{
    private string $title;
    private string $content;
    private bool $public;
    private array $tags;

    public function __construct(string $title, string $content, bool $public, array $tags)
    {
        $this->title = $title;
        $this->content = $content;
        $this->public = $public;
        $this->tags = $tags;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function isPublic(): bool
    {
        return $this->public;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}
