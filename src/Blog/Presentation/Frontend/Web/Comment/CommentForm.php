<?php

declare(strict_types=1);

namespace App\Blog\Presentation\Frontend\Web\Comment;

use App\Blog\Domain\Comment;
use Yiisoft\Form\FormModel;
use Yiisoft\Validator\Rule\HasLength;
use Yiisoft\Validator\Rule\Required;

final class CommentForm extends FormModel
{
    private string $comment;

    public function __construct(?Comment $comment)
    {
        $this->comment = $comment ? $comment->getContent() : '';
        parent::__construct();
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function getFormName(): string
    {
        return '';
    }

    public function getRules(): array
    {
        return [
            'comment' => [
                Required::rule(),
                HasLength::rule()->min(3)->max(191),
            ],
        ];
    }
}
