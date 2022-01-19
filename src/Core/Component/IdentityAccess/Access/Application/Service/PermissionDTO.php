<?php

declare(strict_types=1);

namespace App\Core\Component\IdentityAccess\Access\Application\Service;

class PermissionDTO
{
    private string $name;
    private ?string $description;
    private ?int $created_at;
    private ?int $updated_at;

    public function __construct(
        string $name,
        ?string $description = null,
        ?int $created_at = null,
        ?int $updated_at = null
    ) {
        $this->name = $name;
        $this->description = $description;
        $this->created_at = $created_at;
        $this->updated_at = $updated_at;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
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
