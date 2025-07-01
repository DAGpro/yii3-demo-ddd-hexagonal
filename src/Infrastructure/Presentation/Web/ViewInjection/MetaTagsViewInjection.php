<?php

declare(strict_types=1);

namespace App\Infrastructure\Presentation\Web\ViewInjection;

use Yiisoft\Yii\View\Renderer\MetaTagsInjectionInterface;

final class MetaTagsViewInjection implements MetaTagsInjectionInterface
{
    #[\Override]
    public function getMetaTags(): array
    {
        return [
            'generator' => [
                'name' => 'generator',
                'content' => 'Yii',
            ],
        ];
    }
}
