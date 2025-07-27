<?php

declare(strict_types=1);

namespace App\Site\Presentation\Frontend\Web\Contact;

use Override;
use Yiisoft\FormModel\FormModel;
use Yiisoft\Validator\Rule\Each;
use Yiisoft\Validator\Rule\Email;
use Yiisoft\Validator\Rule\Image\Image;
use Yiisoft\Validator\Rule\Required;
use Yiisoft\Validator\RulesProviderInterface;

final class ContactForm extends FormModel implements RulesProviderInterface
{
    private string $name = '';
    private string $email = '';
    private string $subject = '';
    private string $body = '';
    private ?array $attachFiles = null;

    #[Override]
    public function getPropertyLabels(): array
    {
        return [
            'name' => 'Name',
            'email' => 'Email',
            'subject' => 'Subject',
            'body' => 'Body',
        ];
    }

    #[Override]
    public function getRules(): array
    {
        return [
            'name' => [new Required()],
            'email' => [new Required(), new Email()],
            'subject' => [new Required()],
            'body' => [new Required()],
            'attachFiles' => [
                new Each(
                    new Image(),
                ),
            ],
        ];
    }
}
