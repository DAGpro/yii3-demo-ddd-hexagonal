<?php

declare(strict_types=1);

use App\Site\Presentation\Frontend\Web\Contact\ContactMailer;

/** @var array $params */

return [
    ContactMailer::class => [
        'class' => ContactMailer::class,
        '__construct()' => [
            'sender' => $params['mailer']['senderEmail'],
            'to' => $params['mailer']['adminEmail'],
        ],
    ],
];
