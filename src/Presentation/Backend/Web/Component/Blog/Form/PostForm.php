<?php

declare(strict_types=1);

namespace App\Presentation\Backend\Web\Component\Blog\Form;

use App\Core\Component\Blog\Domain\Post;
use App\Core\Component\Blog\Domain\Tag;
use Yiisoft\Form\FormModel;
use Yiisoft\Validator\Rule\Each;
use Yiisoft\Validator\Rule\HasLength;
use Yiisoft\Validator\Rule\Required;
use Yiisoft\Validator\Rules;

final class PostForm extends FormModel
{
    private string $title;
    private string $content;
    private bool $public;
    private array $tags;

    public function __construct(?Post $post)
    {
        $this->title = $post ? $post->getTitle() : '';
        $this->content = $post ? $post->getContent() : '';
        $this->public = $post && $post->isPublic();
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

    public function getPublic(): bool
    {
        return $this->public;
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
        $rules = new Rules();
        $rules->add(Required::rule());
        return [
            'title' => [
                Required::rule(),
                HasLength::rule()->min(4)->max(255),
            ],
            'content' => [
                Required::rule(),
            ],
            'tags' => [
                Each::rule($rules),
            ],
        ];
    }

}
