<?php

declare(strict_types=1);

namespace App\Core\Component\Blog\Domain\User;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Embeddable;

/** @Embeddable  */
class Author
{
    /** @Column(type = "string", nullable = true) */
    private int $authorId;

    /** @Column(type = "string", nullable = true) */
    private string $authorName;

    public function __construct(?int $id, ?string $name)
    {
        $this->authorId = $id;
        $this->authorName = $name;
    }

    public function getId(): ?int
    {
        return $this->authorId;
    }

    public function getName(): ?string
    {
        return $this->authorName;
    }

    public function isEqual(Author $author): bool
    {
        return $this->authorId === $author->getId() && $this->authorName === $author->getName();
    }
}
