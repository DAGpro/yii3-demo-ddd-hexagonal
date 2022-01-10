<?php

declare(strict_types=1);

namespace App\Core\Component\IdentityAccess\User\Domain;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Table;
use Cycle\Annotated\Annotation\Table\Index;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Yiisoft\Security\PasswordHasher;

/**
 * @Entity(repository="App\Core\Component\IdentityAccess\User\Infrastructure\Persistence\UserRepository")
 * @Table(
 *     indexes={
 *         @Index(columns={"login"}, unique=true),
 *     }
 * )
 */
class User
{
    /**
     * @Column(type="primary")
     */
    private ?int $id = null;

    /**
     * @Column(type="string(48)")
     */
    private string $login;

    /**
     * @Column(type="string")
     */
    private string $passwordHash;

    /**
     * @Column(type="datetime")
     */
    private DateTimeImmutable $created_at;

    /**
     * @Column(type="datetime")
     */
    private DateTimeImmutable $updated_at;

    public function __construct(string $login, string $password)
    {
        $this->login = $login;
        $this->created_at = new DateTimeImmutable();
        $this->updated_at = new DateTimeImmutable();
        $this->setPassword($password);
        $this->posts = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function setLogin(string $login): void
    {
        $this->login = $login;
    }

    public function validatePassword(string $password): bool
    {
        return (new PasswordHasher())->validate($password, $this->passwordHash);
    }

    public function setPassword(string $password): void
    {
        $this->passwordHash = (new PasswordHasher())->hash($password);
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
