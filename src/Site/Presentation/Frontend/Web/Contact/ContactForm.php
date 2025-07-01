<?php

declare(strict_types=1);

namespace App\Site\Presentation\Frontend\Web\Contact;

use Yiisoft\FormModel\FormModel;
use Yiisoft\Validator\Rule\Email;
use Yiisoft\Validator\Rule\Required;

final class ContactForm extends FormModel
{
    private string $name = '';
    private string $email = '';
    private string $subject = '';
    private string $body = '';
    private ?array $attachFiles = null;

    #[\Override]
    public function getPropertyLabels(): array
    {
        return [
            'name' => 'Name',
            'email' => 'Email',
            'subject' => 'Subject',
            'body' => 'Body',
        ];
    }

    #[\Override]
    public function getFormName(): string
    {
        return 'ContactForm';
    }

    public function getRules(): array
    {
        return [
            'name' => [new Required()],
            'email' => [new Required(), new Email()],
            'subject' => [new Required()],
            'body' => [new Required()],
        ];
    }
}
