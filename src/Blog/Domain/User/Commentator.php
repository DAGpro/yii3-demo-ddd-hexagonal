<?php

declare(strict_types=1);

namespace App\Blog\Domain\User;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Embeddable;

/**
 * @psalm-suppress ClassMustBeFinal
 */
#[Embeddable]
class Commentator
{
    public function __construct(
        #[Column(type: 'integer')]
        private readonly int $commentatorId,
        #[Column(type: 'string(191)')]
        private readonly string $commentatorName,
    ) {
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
