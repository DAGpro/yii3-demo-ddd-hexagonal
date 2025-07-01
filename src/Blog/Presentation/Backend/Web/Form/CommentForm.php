<?php

declare(strict_types=1);

namespace App\Blog\Presentation\Backend\Web\Form;

use App\Blog\Domain\Comment;
use phpDocumentor\Reflection\Types\Boolean;
use Yiisoft\FormModel\FormModel;
use Yiisoft\Validator\Rule\Number;
use Yiisoft\Validator\Rule\Required;

final class CommentForm extends FormModel
{
    private readonly ?int $comment_id;
    private readonly string $content;
    private readonly bool $public;

    public function __construct(?Comment $comment)
    {
        $this->comment_id = $comment?->getId();
        $this->content = $comment ? $comment->getContent() : '';
        $this->public = $comment && $comment->isPublic();
    }

    public function getCommentId(): ?int
    {
        return $this->comment_id;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getPublic(): bool
    {
        return $this->public;
    }

    #[\Override]
    public function getPropertyLabels(): array
    {
        return [
            'comment_id' => 'Comment Id',
            'content' => 'Content',
            'public' => 'Publish?',
        ];
    }

    #[\Override]
    public function getFormName(): string
    {
        return '';
    }

    public function getRules(): array
    {
        return [
            'content' => [
                new Required(),
            ],
            'comment_id' => [
                new Required(),
                new Number(),
            ],
            'public' => [
                new Boolean(),
            ],
        ];
    }
}
