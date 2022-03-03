<?php

declare(strict_types=1);

namespace App\Blog\Presentation\Backend\Web\Form;

use App\Blog\Domain\Tag;
use Yiisoft\Form\FormModel;
use Yiisoft\Validator\Rule\HasLength;
use Yiisoft\Validator\Rule\Number;
use Yiisoft\Validator\Rule\Required;

final class TagForm extends FormModel
{
    private string $id;
    private string $label;

    public function __construct(?Tag $tag)
    {
        $this->id = $tag ? (string)$tag->getId() : '';
        $this->label = $tag ? $tag->getLabel() : '';
        parent::__construct();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getFormName(): string
    {
        return '';
    }

    public function getRules(): array
    {
        return [
            'id' => [
                Required::rule(),
                Number::rule(),
            ],
            'label' => [
                Required::rule(),
                HasLength::rule()->min(3)->max(191),
            ],
        ];
    }

}
