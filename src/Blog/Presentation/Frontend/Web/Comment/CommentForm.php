<?php

declare(strict_types=1);

namespace App\Blog\Presentation\Frontend\Web\Comment;

use App\Blog\Domain\Comment;
use Override;
use Yiisoft\FormModel\FormModel;
use Yiisoft\Validator\Rule\Length;
use Yiisoft\Validator\Rule\Required;
use Yiisoft\Validator\RulesProviderInterface;

final class CommentForm extends FormModel implements RulesProviderInterface
{
    private ?string $comment;

    public function __construct(?Comment $comment = null)
    {
        $this->comment = $comment?->getContent();
    }

    public function getComment(): ?string
    {
        return $this->comment;
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
            'comment' => [
                new Required(),
                new Length(min: 3, max: 191),
            ],
        ];
    }
}
