<?php

declare(strict_types=1);

namespace App\Blog\Presentation\Backend\Web\Form;

use App\Blog\Domain\Post;
use App\Blog\Domain\Tag;
use Override;
use Yiisoft\FormModel\FormModel;
use Yiisoft\Validator\Rule\Each;
use Yiisoft\Validator\Rule\Length;
use Yiisoft\Validator\Rule\Required;


final class PostForm extends FormModel
{
    private readonly string $title;
    private readonly string $content;
    private readonly bool $public;
    private readonly array $tags;

    public function __construct(?Post $post)
    {
        $this->title = $post ? $post->getTitle() : '';
        $this->content = $post ? $post->getContent() : '';
        $this->public = $post && $post->isPublic();
        $this->tags = $post ? array_map(static fn(Tag $tag) => $tag->getLabel(), $post->getTags()) : [];
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

    public function getTags(): array
    {
        return $this->tags;
    }

    #[Override]
    public function getFormName(): string
    {
        return '';
    }

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
