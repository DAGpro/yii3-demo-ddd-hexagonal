<?php

declare(strict_types=1);

namespace App\Blog\Domain;

use App\Blog\Domain\User\Commentator;
use App\Blog\Infrastructure\Persistence\Comment\CommentRepository;
use App\Blog\Infrastructure\Persistence\Post\Scope\PublicAndNotDeletedConstrain;
use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\BelongsTo;
use Cycle\Annotated\Annotation\Relation\Embedded;
use Cycle\Annotated\Annotation\Table\Index;
use Cycle\ORM\Entity\Behavior;
use DateTimeImmutable;

/**
 * @psalm-suppress ClassMustBeFinal
 */
#[Entity(
    repository: CommentRepository::class,
    scope: PublicAndNotDeletedConstrain::class
)]
#[Index(columns: ['public', 'published_at'])]
#[Behavior\CreatedAt(field: 'created_at', column: 'created_at')]
#[Behavior\UpdatedAt(field: 'updated_at', column: 'updated_at')]
#[Behavior\SoftDelete(field: 'deleted_at', column: 'deleted_at')]
class Comment
{
    #[Column(type: 'primary')]
    public ?int $id = null;

    #[Column(type: 'bool', default: 'false', typecast: 'bool')]
    private bool $public = false;

    /**
     * @psalm-suppress MissingPropertyNotSetInConstructor
     */
    #[BelongsTo(target: Post::class, nullable: false)]
    private Post $post;

    #[Column(type: 'datetime')]
    private readonly DateTimeImmutable $created_at;

    #[Column(type: 'datetime')]
    private readonly DateTimeImmutable $updated_at;

    #[Column(type: 'datetime', nullable: true)]
    private ?DateTimeImmutable $published_at = null;

    #[Column(type: 'datetime', nullable: true)]
    private ?DateTimeImmutable $deleted_at = null;

    public function __construct(
        #[Column(type: 'text')]
        private string $content,
        Post $post,
        #[Embedded(target: Commentator::class)]
        private Commentator $commentator,
    ) {
        $this->post = $post;
        $this->created_at = new DateTimeImmutable();
        $this->updated_at = new DateTimeImmutable();
    }

    public function publish(): void
    {
        $this->public = true;
        $this->published_at = new DateTimeImmutable();
    }

    public function toDraft(): void
    {
        $this->public = false;
        $this->published_at = null;
    }

    public function change(
        string $content,
        ?Post $post = null,
        ?Commentator $commentator = null,
    ): void {
        $this->content = $content;

        if ($post !== null) {
            $this->post = $post;
        }

        if ($commentator !== null) {
            $this->commentator = $commentator;
        }
    }

    public function isPublic(): bool
    {
        return $this->public;
    }

    public function isCommentator(Commentator $commentator): bool
    {
        return $this->commentator->isEqual($commentator);
    }

    public function changeCommentator(Commentator $commentator): void
    {
        $this->commentator = $commentator;
    }

    public function getCommentator(): Commentator
    {
        return $this->commentator;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->created_at;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function getPublishedAt(): ?DateTimeImmutable
    {
        return $this->published_at;
    }

    public function setPublishedAt(DateTimeImmutable $date): void
    {
        $this->published_at = $date;
    }

    public function getDeletedAt(): ?DateTimeImmutable
    {
        return $this->deleted_at;
    }

}
