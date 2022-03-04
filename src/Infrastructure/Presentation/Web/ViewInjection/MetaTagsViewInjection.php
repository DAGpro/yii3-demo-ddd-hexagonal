<?php

declare(strict_types=1);

namespace App\Infrastructure\Presentation\Web\ViewInjection;

use Yiisoft\Yii\View\MetaTagsInjectionInterface;

final class MetaTagsViewInjection implements MetaTagsInjectionInterface
{
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
