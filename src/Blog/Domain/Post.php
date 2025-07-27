<?php

declare(strict_types=1);

namespace App\Blog\Domain;

use App\Blog\Domain\User\Author;
use App\Blog\Domain\User\Commentator;
use App\Blog\Infrastructure\Persistence\Post\PostRepository;
use App\Blog\Infrastructure\Persistence\Post\PostTag;
use App\Blog\Infrastructure\Persistence\Post\Scope\PublicAndNotDeletedConstrain;
use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\Embedded;
use Cycle\Annotated\Annotation\Relation\HasMany;
use Cycle\Annotated\Annotation\Relation\ManyToMany;
use Cycle\Annotated\Annotation\Table\Index;
use Cycle\ORM\Collection\Pivoted\PivotedCollection;
use Cycle\ORM\Entity\Behavior;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Yiisoft\Security\Random;

/**
 * @psalm-suppress ClassMustBeFinal
 */
#[Entity(
    repository: PostRepository::class,
    scope: PublicAndNotDeletedConstrain::class
)]
#[Index(columns: ['public', 'published_at'])]
#[Behavior\CreatedAt(field: 'created_at', column: 'created_at')]
#[Behavior\UpdatedAt(field: 'updated_at', column: 'updated_at')]
#[Behavior\SoftDelete(field: 'deleted_at', column: 'deleted_at')]

class Post
{
    #[Column(type: 'primary')]
    private ?int $id = null;

    #[Column(type: 'string(128)')]
    private string $slug = '';

    /**
     * @var ArrayCollection<array-key, Comment>
     */
    #[HasMany(target: Comment::class)]
    private readonly ArrayCollection $comments;

    /**
     * @var PivotedCollection<array-key, Tag, PostTag>
     */
    #[ManyToMany(
        target: Tag::class,
        through: PostTag::class,
        fkAction: 'CASCADE'
    )]
    private readonly PivotedCollection $tags;
    private ?int $tag_id = null;

    #[Column(type: 'bool', default: 'false', typecast: 'bool')]
    private bool $public = false;

    #[Column(type: 'datetime')]
    private readonly DateTimeImmutable $created_at;

    #[Column(type: 'datetime')]
    private readonly DateTimeImmutable $updated_at;

    #[Column(type: 'datetime', nullable: true)]
    private ?DateTimeImmutable $published_at = null;

    #[Column(type: 'datetime', nullable: true)]
    private ?DateTimeImmutable $deleted_at = null;

    public function __construct(
        #[Column(type: 'string(191)', default: '')]
        private string $title,
        #[Column(type: 'text')]
        private string $content,
        #[Embedded(target: Author::class)]
        private Author $author,
    ) {
        /** @var PivotedCollection<array-key, Tag, PostTag> $tags */
        $tags = new PivotedCollection();
        $this->tags = $tags;
        $this->comments = new ArrayCollection();
        $this->created_at = new DateTimeImmutable();
        $this->updated_at = new DateTimeImmutable();

        $this->resetSlug();
    }

    public function edit(
        string $title,
        string $content,
        ?Author $author = null,
    ): void {
        $this->title = $title;
        $this->content = $content;

        if ($author !== null) {
            $this->author = $author;
        }
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

    public function isAuthor(Author $author): bool
    {
        return $this->author->isEqual($author);
    }

    public function changeAuthor(Author $author): void
    {
        $this->author = $author;
    }

    public function getAuthor(): Author
    {
        return $this->author;
    }

    public function createComment(string $comment, Commentator $commentator): Comment
    {
        return new Comment($comment, $this, $commentator);
    }

    /**
     * @return array<array-key, Comment>
     */
    public function getComments(): array
    {
        return $this->comments->toArray();
    }

    /** Getters Setters */
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function resetSlug(): void
    {
        $this->slug = Random::string(128);
    }

    /**
     * @return array<array-key, Tag>
     * @psalm-return Tag[]
     */
    public function getTags(): array
    {
        /** @var Tag[] $tags */
        $tags = $this->tags->toArray();
        return $tags;
    }

    public function addTag(Tag $post): void
    {
        $this->tags->add($post);
    }

    public function resetTags(): void
    {
        $this->tags->clear();
    }

    public function isNewRecord(): bool
    {
        return $this->getId() === null;
    }

    public function isPublic(): bool
    {
        return $this->public;
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
