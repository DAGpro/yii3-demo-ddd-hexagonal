<?php

declare(strict_types=1);

namespace App\Blog\Presentation\Frontend\Web\Comment;

use App\Blog\Domain\Comment;
use Yiisoft\FormModel\FormModel;
use Yiisoft\Validator\Rule\Length;
use Yiisoft\Validator\Rule\Required;

final class CommentForm extends FormModel
{
    private readonly string $comment;

    public function __construct(?Comment $comment)
    {
        $this->comment = $comment ? $comment->getContent() : '';
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    #[\Override]
    public function getFormName(): string
    {
        return '';
    }

    public function getRules(): array
    {
        return [
            'comment' => [
                new Required(),
                new Length(min: 3, max: 191),
            ],
        ];
    }
}
