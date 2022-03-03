<?php

declare(strict_types=1);

namespace App\Blog\Presentation\Backend\Web\Form;

use App\Blog\Domain\Comment;
use Yiisoft\Form\FormModel;
use Yiisoft\Validator\Rule\Boolean;
use Yiisoft\Validator\Rule\Number;
use Yiisoft\Validator\Rule\Required;

final class CommentForm extends FormModel
{
    private ?int $comment_id;
    private string $content;
    private bool $public;

    public function __construct(?Comment $comment)
    {
        $this->comment_id = $comment ? $comment->getId() : null;
        $this->content = $comment ? $comment->getContent() : '';
        $this->public = $comment && $comment->isPublic();
        parent::__construct();
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

    public function getAttributeLabels(): array
    {
        return [
            'comment_id' => 'Comment Id',
            'content' => 'Content',
            'public' => 'Publish?'
        ];
    }

    public function getFormName(): string
    {
        return '';
    }

    public function getRules(): array
    {
        return [
            'content' => [
                Required::rule(),
            ],
            'comment_id' => [
                Required::rule(),
                Number::rule(),
            ],
            'public' => [
                Boolean::rule(),
            ]
        ];
    }

}
