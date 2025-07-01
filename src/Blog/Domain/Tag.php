<?php

declare(strict_types=1);

namespace App\Blog\Domain;

use App\Blog\Infrastructure\Persistence\Post\PostTag;
use App\Blog\Infrastructure\Persistence\Tag\TagRepository;
use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\ManyToMany;
use Cycle\Annotated\Annotation\Table\Index;
use Cycle\ORM\Collection\Pivoted\PivotedCollection;
use Cycle\ORM\Entity\Behavior;
use DateTimeImmutable;

#[Entity(repository: TagRepository::class)]
#[Index(columns: ['label'], unique: true)]
#[Behavior\CreatedAt(field: 'created_at', column: 'created_at')]
class Tag
{
    #[Column(type: 'primary')]
    private ?int $id = null;

    #[Column(type: 'datetime')]
    private readonly DateTimeImmutable $created_at;

    /**
     * @var PivotedCollection<array-key, Post, PostTag>
     */
    #[ManyToMany(target: Post::class, through: PostTag::class, fkAction: 'CASCADE', indexCreate: false)]
    private readonly PivotedCollection $posts;

    public function __construct(#[Column(type: 'string(191)')]
    private string $label)
    {
        $this->created_at = new DateTimeImmutable();
        $this->posts = new PivotedCollection();
    }

    public function change(string $label): void
    {
        $this->label = $label;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return Post[]
     */
    public function getPosts(): array
    {
        return $this->posts->toArray();
    }

    public function addPost(Post $post): void
    {
        $this->posts->add($post);
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->created_at;
    }

}
