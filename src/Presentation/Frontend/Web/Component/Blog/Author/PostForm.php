<?php

declare(strict_types=1);

namespace App\Presentation\Frontend\Web\Component\Blog\Author;

use App\Blog\Domain\Post;
use App\Blog\Domain\Tag;
use Yiisoft\Form\FormModel;
use Yiisoft\Validator\Rule\Each;
use Yiisoft\Validator\Rule\HasLength;
use Yiisoft\Validator\Rule\Required;
use Yiisoft\Validator\RuleSet;

final class PostForm extends FormModel
{
    private string $title;
    private string $content;
    private array $tags;

    public function __construct(?Post $post)
    {
        $this->title = $post ? $post->getTitle() : '';
        $this->content = $post ? $post->getContent() : '';
        $this->tags = $post ? array_map(static fn (Tag $tag) => $tag->getLabel(), $post->getTags()) : [];
        parent::__construct();
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function getFormName(): string
    {
        return '';
    }

    public function getRules(): array
    {
        $rules = new RuleSet();
        $rules->add(HasLength::rule()->min(3));
        return [
            'title' => [
                Required::rule(),
                HasLength::rule()->min(4)->max(191),
            ],
            'content' => [
                Required::rule(),
                HasLength::rule()->min(4),
            ],
            'tags' => [
                Each::rule($rules),
            ],
        ];
    }
}
