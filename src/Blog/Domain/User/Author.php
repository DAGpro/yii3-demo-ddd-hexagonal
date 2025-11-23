<?php

declare(strict_types=1);

namespace App\Blog\Domain\User;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Embeddable;

/**
 * @psalm-suppress ClassMustBeFinal
 */
#[Embeddable]
class Author
{
    public function __construct(
        #[Column(type: 'integer')]
        private readonly int $authorId,
        #[Column(type: 'string(191)')]
        private readonly string $authorName,
    ) {}

    public function getId(): int
    {
        return $this->authorId;
    }

    public function getName(): string
    {
        return $this->authorName;
    }

    public function isEqual(Author $author): bool
    {
        return $this->authorId === $author->getId() && $this->authorName === $author->getName();
    }
}
