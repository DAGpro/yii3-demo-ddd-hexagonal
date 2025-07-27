<?php

declare(strict_types=1);

namespace App\IdentityAccess\User\Infrastructure\Authentication;

use App\IdentityAccess\User\Domain\User;
use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\BelongsTo;
use Override;
use Yiisoft\Security\Random;
use Yiisoft\User\Login\Cookie\CookieLoginIdentityInterface;

/**
 * @psalm-suppress ClassMustBeFinal
 */
#[Entity(repository: IdentityRepository::class)]
class Identity implements CookieLoginIdentityInterface
{
    #[Column(type: 'primary')]
    private ?int $id = null;

    #[Column(type: 'string(32)')]
    private string $authKey;

    public function __construct(
        #[BelongsTo(target: User::class, nullable: false, load: 'eager')]
        private readonly User $user,
    ) {
        $this->authKey = '';
        $this->regenerateCookieLoginKey();
    }

    #[Override]
    public function getId(): ?string
    {
        return (string)$this->id;
    }

    #[Override]
    public function getCookieLoginKey(): string
    {
        return $this->authKey;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    #[Override]
    public function validateCookieLoginKey(string $key): bool
    {
        return $this->authKey === $key;
    }

    public function regenerateCookieLoginKey(): void
    {
        $this->authKey = Random::string(32);
    }
}
