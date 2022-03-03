<?php

declare(strict_types=1);

namespace App\Blog\Domain\User;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Embeddable;

#[Embeddable]
class Commentator
{
    #[Column(type:'integer')]
    private int $commentatorId;

    #[Column(type: 'string(191)')]
    private string $commentatorName;

    public function __construct(int $id, string $name)
    {
        $this->commentatorId = $id;
        $this->commentatorName = $name;
    }

    public function getId(): int
    {
        return $this->commentatorId;
    }

    public function getName(): string
    {
        return $this->commentatorName;
    }

    public function isEqual(Commentator $commentator): bool
    {
        return $this->commentatorId === $commentator->getId()
            && $this->commentatorName === $commentator->getName();
    }
}
