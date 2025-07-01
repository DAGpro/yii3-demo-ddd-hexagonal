<?php

declare(strict_types=1);

namespace App\Infrastructure\Presentation\Web\ViewInjection;

use Yiisoft\Yii\View\Renderer\LinkTagsInjectionInterface;

final class LinkTagsViewInjection implements LinkTagsInjectionInterface
{
    #[\Override]
    public function getLinkTags(): array
    {
        return [
            'favicon' => [
                'rel' => 'icon',
                'href' => '/favicon.ico',
            ],
        ];
    }
}
