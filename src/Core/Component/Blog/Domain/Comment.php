<?php

declare(strict_types=1);

namespace App\Core\Component\Blog\Domain;

use App\Core\Component\Blog\Domain\User\Commentator;
use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\BelongsTo;
use Cycle\Annotated\Annotation\Relation\Embedded;
use Cycle\Annotated\Annotation\Table;
use Cycle\Annotated\Annotation\Table\Index;
use DateTimeImmutable;

/**
 * @Entity(
 *     repository="App\Core\Component\Blog\Infrastructure\Persistence\Comment\CommentRepository",
 *     mapper="App\Core\Component\Blog\Infrastructure\Persistence\Comment\CommentMapper",
 * )
 * @Table(
 *     indexes={
 *         @Index(columns={"public","published_at"})
 *     }
 * )
 */
class Comment
{
    /**
     * @Column(type="primary")
     */
    private ?int $id = null;

    /**
     * @Column(type="bool", default="false")
     */
    private bool $public = false;

    /**
     * @Column(type="text")
     */
    private string $content;

    /** @Embedded(target = "App\Core\Component\Blog\Domain\User\Commentator") */
    private Commentator $commentator;

    /**
     * @BelongsTo(target="App\Core\Component\Blog\Domain\Post",  nullable=false)
     *
     * @var \Cycle\ORM\Promise\Reference|Post
     */
    private $post = null;
    private int $post_id;

    /**
     * @Column(type="datetime")
     */
    private DateTimeImmutable $created_at;

    /**
     * @Column(type="datetime")
     */
    private DateTimeImmutable $updated_at;

    /**
     * @Column(type="datetime", nullable=true)
     */
    private ?DateTimeImmutable $published_at = null;

    /**
     * @Column(type="datetime", nullable=true)
     */
    private ?DateTimeImmutable $deleted_at = null;

    public function __construct(
        string $content,
        Post $post,
        Commentator $commentator
    ) {
        $this->content = $content;
        $this->commentator = $commentator;
        $this->post_id = $post->getId();
        $this->created_at = new DateTimeImmutable();
        $this->updated_at = new DateTimeImmutable();
    }

    public function isCommentator($commentator): bool
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

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function isPublic(): bool
    {
        return $this->public;
    }

    public function setPublic(bool $public): void
    {
        $this->public = $public;
    }

    public function setPost(Post $post): void
    {
        $this->post = $post;
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

    public function setPublishedAt(?DateTimeImmutable $published_at): void
    {
        $this->published_at = $published_at;
    }

    public function getDeletedAt(): ?DateTimeImmutable
    {
        return $this->deleted_at;
    }

}
