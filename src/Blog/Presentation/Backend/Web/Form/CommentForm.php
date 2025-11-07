<?php

declare(strict_types=1);

namespace App\Blog\Presentation\Backend\Web\Form;

use App\Blog\Domain\Comment;
use InvalidArgumentException;
use Override;
use Yiisoft\FormModel\FormModel;
use Yiisoft\Validator\Rule\BooleanValue;
use Yiisoft\Validator\Rule\Number;
use Yiisoft\Validator\Rule\Required;
use Yiisoft\Validator\RulesProviderInterface;

final class CommentForm extends FormModel implements RulesProviderInterface
{
    private int $comment_id;
    private string $content;
    private bool $public;

    public function __construct(Comment $comment)
    {
        $id = $comment->getId();
        if ($id === null) {
            throw new InvalidArgumentException('Comment id is null');
        }
        $this->comment_id = $id;
        $this->content = $comment->getContent();
        $this->public = $comment->isPublic();
    }

    public function getCommentId(): int
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

    #[Override]
    public function getPropertyLabels(): array
    {
        return [
            'comment_id' => 'Comment Id',
            'content' => 'Content',
            'public' => 'Publish?',
        ];
    }

    #[Override]
    public function getFormName(): string
    {
        return '';
    }

    #[Override]
    public function getRules(): iterable
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
                new BooleanValue(),
            ],
        ];
    }
}
