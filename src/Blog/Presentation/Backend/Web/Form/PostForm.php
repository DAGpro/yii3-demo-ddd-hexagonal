<?php

declare(strict_types=1);

namespace App\Blog\Presentation\Backend\Web\Form;

use App\Blog\Domain\Post;
use App\Blog\Domain\Tag;
use InvalidArgumentException;
use Override;
use Yiisoft\FormModel\FormModel;
use Yiisoft\Validator\Rule\Each;
use Yiisoft\Validator\Rule\Length;
use Yiisoft\Validator\Rule\Required;
use Yiisoft\Validator\RulesProviderInterface;

final class PostForm extends FormModel implements RulesProviderInterface
{
    private readonly string $title;
    private readonly string $content;
    private readonly bool $public;
    /** @var string[] */
    private readonly array $tags;
    private int $id;

    public function __construct(Post $post)
    {
        $id = $post->getId();
        if ($id === null) {
            throw new InvalidArgumentException('Post id is null');
        }
        $this->id = $id;
        $this->title = $post->getTitle();
        $this->content = $post->getContent();
        $this->public = $post->isPublic();
        $this->tags = array_map(static fn(Tag $tag) => $tag->getLabel(), $post->getTags());
    }

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

    public function getPublic(): bool
    {
        return $this->public;
    }

    /**
     * @return string[]
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    #[Override]
    public function getFormName(): string
    {
        return '';
    }

    #[Override]
    public function getRules(): array
    {
        return [
            'title' => [
                new Required(),
                new Length(min: 4, max: 255),
            ],
            'content' => [
                new Required(),
            ],
            'tags' => [
                new Each([
                    new Required(),
                ]),
            ],
        ];
    }

}
