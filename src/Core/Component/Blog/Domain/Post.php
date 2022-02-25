<?php

declare(strict_types=1);

namespace App\Core\Component\Blog\Domain;

use App\Core\Component\Blog\Domain\User\Author;
use App\Core\Component\Blog\Domain\User\Commentator;
use App\Core\Component\Blog\Infrastructure\Persistence\Post\PostRepository;
use App\Core\Component\Blog\Infrastructure\Persistence\Post\PostTag;
use App\Core\Component\Blog\Infrastructure\Persistence\Post\Scope\PublicScope;
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

#[Entity(
    repository: PostRepository::class,
    scope: PublicScope::class
)]
#[Index(columns: ['public', 'published_at'])]
#[Behavior\CreatedAt(field: 'created_at', column: 'created_at')]
#[Behavior\UpdatedAt(field: 'updated_at', column: 'updated_at')]
#[Behavior\SoftDelete(field: 'deleted_at', column: 'deleted_at')]
class Post
{
    #[Column(type: 'primary')]
    private ?int $id = null;

    #[Column(type: 'string(191)', default: '')]
    private string $title;

    #[Column(type: 'string(191)')]
    private string $content;

    #[Column(type: 'string(128)')]
    private string $slug;

    #[Embedded(target: Author::class)]
    private Author $author;

    #[HasMany(target: Comment::class)]
    private ArrayCollection $comments;

    /**
     * @var PivotedCollection<array-key, Tag, PostTag>
     */
    #[ManyToMany(target: Tag::class, though: PostTag::class, fkAction: 'CASCADE')]
    private PivotedCollection $tags;
    private ?int $tag_id = null;

    #[Column(type: 'bool', default: 'false', typecast: 'bool')]
    private bool $public = false;

    #[Column(type: 'datetime')]
    private DateTimeImmutable $created_at;

    #[Column(type: 'datetime')]
    private DateTimeImmutable $updated_at;

    #[Column(type: 'datetime', nullable: true)]
    private ?DateTimeImmutable $published_at = null;

    #[Column(type: 'datetime', nullable: true)]
    private ?DateTimeImmutable $deleted_at = null;

    public function __construct(string $title, string $content, Author $author)
    {
        $this->title = $title;
        $this->content = $content;
        $this->author = $author;
        $this->tags = new PivotedCollection();
        $this->comments = new ArrayCollection();
        $this->created_at = new DateTimeImmutable();
        $this->updated_at = new DateTimeImmutable();

        $this->resetSlug();
    }

    public function editPost(string $title, string $content): void
    {
        $this->title = $title;
        $this->content = $content;
    }

    public function isAuthor(Author $author) :bool
    {
        return $this->author->isEqual($author);
    }

    public function changeAuthor(Author $author) :void
    {
        $this->author = $author;
    }

    public function getAuthor(): Author
    {
        return $this->author;
    }

    /**
     * @return Comment[]
     */
    public function getComments(): array
    {
        return $this->comments->toArray();
    }

    public function createComment(string $comment, Commentator $commentator): Comment
    {
        return new Comment($comment, $this, $commentator);
    }

    /** Getters Setters */
    public function getId(): int
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function resetSlug(): void
    {
        $this->slug = Random::string(128);
    }

    /**
     * @return Tag[]
     */
    public function getTags(): array
    {
        return $this->tags->toArray();
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

    public function setPublic(bool $public): void
    {
        $this->public = $public;
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

    public function setPublishedAt(?DateTimeImmutable $published_at): void
    {
        $this->published_at = $published_at;
    }

    public function getDeletedAt(): ?DateTimeImmutable
    {
        return $this->deleted_at;
    }

}
