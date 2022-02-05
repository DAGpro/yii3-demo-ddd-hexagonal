<?php

declare(strict_types=1);

namespace App\Core\Component\Blog\Application\Service\CommandService;

final class PostCreateDTO
{
    private string $title;
    private string $content;
    private array $tags;

    public function __construct(string $title, string $content, array $tags)
    {
        $this->title = $title;
        $this->content = $content;
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

    public function getContent(): string
    {
        return $this->content;
    }
}
