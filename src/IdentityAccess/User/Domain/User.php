<?php

declare(strict_types=1);

namespace App\IdentityAccess\User\Domain;


use App\IdentityAccess\User\Infrastructure\Persistence\UserRepository;
use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Table\Index;
use Cycle\ORM\Entity\Behavior;
use DateTimeImmutable;
use Yiisoft\Security\PasswordHasher;


/**
 * @psalm-suppress ClassMustBeFinal
 */
#[Entity(repository: UserRepository::class)]
#[Index(columns: ['login'], unique: true)]
#[Behavior\CreatedAt(field: 'created_at', column: 'created_at')]
#[Behavior\UpdatedAt(field: 'updated_at', column: 'updated_at')]
class User
{
    #[Column(type: 'primary')]
    private ?int $id = null;

    #[Column(type: 'string')]
    private string $passwordHash;

    #[Column(type: 'datetime')]
    private readonly DateTimeImmutable $created_at;

    #[Column(type: 'datetime')]
    private readonly DateTimeImmutable $updated_at;

    public function __construct(
        #[Column(type: 'string(48)')]
        private readonly string $login,
        string $password,
    ) {
        $this->created_at = new DateTimeImmutable();
        $this->updated_at = new DateTimeImmutable();
        $this->passwordHash = '';
        $this->setPassword($password);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function validatePassword(string $password): bool
    {
        return new PasswordHasher()->validate($password, $this->passwordHash);
    }

    public function setPassword(string $password): void
    {
        $this->passwordHash = new PasswordHasher()->hash($password);
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->created_at;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updated_at;
    }

}
