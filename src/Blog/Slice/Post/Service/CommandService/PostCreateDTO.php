<?php

declare(strict_types=1);

namespace App\Blog\Slice\Post\Service\CommandService;

final readonly class PostCreateDTO
{
    public function __construct(
        private string $title,
        private string $content,
        /** @var string[] */
        private array $tags,
    ) {}

    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string[]
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}
