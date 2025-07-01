<?php

declare(strict_types=1);

namespace App\Blog\Presentation\Backend\Web\Form;

use App\Blog\Domain\Tag;
use Yiisoft\FormModel\FormModel;
use Yiisoft\Validator\Rule\Length;
use Yiisoft\Validator\Rule\Number;
use Yiisoft\Validator\Rule\Required;

final class TagForm extends FormModel
{
    private readonly string $id;
    private readonly string $label;

    public function __construct(?Tag $tag)
    {
        $this->id = (string)$tag?->getId();
        $this->label = $tag ? $tag->getLabel() : '';
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    #[\Override]
    public function getFormName(): string
    {
        return '';
    }

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
