<?php

declare(strict_types=1);

namespace App\Infrastructure\Authentication;

use App\IdentityAccess\User\Domain\User;
use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\BelongsTo;
use Yiisoft\Security\Random;
use Yiisoft\User\Login\Cookie\CookieLoginIdentityInterface;

#[Entity(repository: IdentityRepository::class)]
class Identity implements CookieLoginIdentityInterface
{
    #[Column(type: 'primary')]
    private ?int $id = null;

    #[Column(type: 'string(32)')]
    private string $authKey;

    #[BelongsTo(target: User::class, nullable: false, load: 'eager')]
    private User $user;
    private ?int $user_id = null;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->regenerateCookieLoginKey();
    }

    public function getId(): ?string
    {
        return (string)$this->id;
    }

    public function getCookieLoginKey(): string
    {
        return $this->authKey;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function validateCookieLoginKey(string $key): bool
    {
        return $this->authKey === $key;
    }

    public function regenerateCookieLoginKey(): void
    {
        $this->authKey = Random::string(32);
    }
}
