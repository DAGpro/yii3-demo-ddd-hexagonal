<?php

declare(strict_types=1);

namespace App\Core\Component\Blog\Domain\User;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Embeddable;

#[Embeddable]
class Author
{
    #[Column(type: 'integer')]
    private int $authorId;

    #[Column(type: 'string(191)')]
    private string $authorName;

    public function __construct(int $id, string $name)
    {
        $this->authorId = $id;
        $this->authorName = $name;
    }

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
