<?php

declare(strict_types=1);

namespace App\Infrastructure\Authentication;

use App\Core\Component\IdentityAccess\User\Domain\User;
use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\BelongsTo;
use Yiisoft\Security\Random;
use Yiisoft\User\Login\Cookie\CookieLoginIdentityInterface;

/**
 * @Entity(repository="App\Infrastructure\Authentication\IdentityRepository")
 */
class Identity implements CookieLoginIdentityInterface
{
    /**
     * @Column(type="primary")
     */
    private ?string $id = null;

    /**
     * @Column(type="string(32)")
     */
    private string $authKey;

    /**
     * @BelongsTo(target="App\Core\Component\IdentityAccess\User\Domain\User", nullable=false, load="eager")
     *
     * @var \Cycle\ORM\Promise\Reference|User
     */
    private $user;
    private ?int $user_id = null;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->regenerateCookieLoginKey();
    }

    public function getId(): ?string
    {
        return $this->id;
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
