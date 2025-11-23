<?php

declare(strict_types=1);

namespace App\Blog\Presentation\Frontend\Web\Author;

use App\Blog\Domain\Post;
use App\Blog\Domain\Tag;
use Override;
use Yiisoft\FormModel\FormModel;
use Yiisoft\Validator\Rule\Each;
use Yiisoft\Validator\Rule\Length;
use Yiisoft\Validator\Rule\Required;
use Yiisoft\Validator\RulesProviderInterface;

final class PostForm extends FormModel implements RulesProviderInterface
{
    private string $title;
    private string $content;

    /** @var string[] */
    private array $tags;

    public function __construct(?Post $post = null)
    {
        $this->title = $post ? $post->getTitle() : '';
        $this->content = $post ? $post->getContent() : '';
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
                new Length(min: 3, max: 191),
            ],
            'content' => [
                new Required(),
                new Length(min: 4),
            ],
            'tags' => [
                new Each(
                    [
                        new Length(min: 3),
                    ],
                ),
            ],
        ];
    }
}
