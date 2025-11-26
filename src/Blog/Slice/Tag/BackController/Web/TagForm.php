<?php

declare(strict_types=1);

namespace App\Blog\Slice\Tag\BackController\Web;

use App\Blog\Domain\Tag;
use InvalidArgumentException;
use Override;
use Yiisoft\FormModel\FormModel;
use Yiisoft\Validator\Rule\Length;
use Yiisoft\Validator\Rule\Number;
use Yiisoft\Validator\Rule\Required;
use Yiisoft\Validator\RulesProviderInterface;

final class TagForm extends FormModel implements RulesProviderInterface
{
    private int $id;
    private string $label;

    public function __construct(Tag $tag)
    {
        $id = $tag->getId();
        if ($id === null) {
            throw new InvalidArgumentException('Tag id is null');
        }
        $this->id = $id;
        $this->label = $tag->getLabel();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getLabel(): string
    {
        return $this->label;
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
            'id' => [
                new Required(),
                new Number(),
            ],
            'label' => [
                new Required(),
                new Length(min: 3, max: 191),
            ],
        ];
    }

}
