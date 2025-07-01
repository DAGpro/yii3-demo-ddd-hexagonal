<?php

declare(strict_types=1);

namespace App\Infrastructure\Presentation\Web\ViewInjection;

use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Yii\View\Renderer\CommonParametersInjectionInterface;

final readonly class CommonViewInjection implements CommonParametersInjectionInterface
{
    public function __construct(private UrlGeneratorInterface $url)
    {
    }

    #[\Override]
    public function getCommonParameters(): array
    {
        return [
            'url' => $this->url,
        ];
    }
}
