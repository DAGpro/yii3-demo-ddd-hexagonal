<?php

declare(strict_types=1);

namespace App\Blog\Application\Service\CommandService;

final readonly class PostChangeDTO
{
    public function __construct(
        private string $title,
        private string $content,
        /** @var string[] */
        private array $tags,
    ) {
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @return string[]
     */
    public function getTags(): array
    {
        return $this->tags;
    }
}
