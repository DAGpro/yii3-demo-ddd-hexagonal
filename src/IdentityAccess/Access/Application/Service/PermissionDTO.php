<?php

declare(strict_types=1);

namespace App\IdentityAccess\Access\Application\Service;

final readonly class PermissionDTO
{
    public function __construct(
        private string $name,
        private ?string $description = null,
        private ?int $created_at = null,
        private ?int $updated_at = null,
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description ?: '';
    }

    public function getCreatedAt(): ?int
    {
        return $this->created_at;
    }

    public function getUpdatedAt(): ?int
    {
        return $this->updated_at;
    }
}
