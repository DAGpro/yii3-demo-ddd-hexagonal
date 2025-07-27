<?php

declare(strict_types=1);

namespace App\Infrastructure\Presentation\Web\Asset;

use Yiisoft\Assets\AssetBundle;
use Yiisoft\Assets\AssetManager;

/**
 * @psalm-import-type CssFile from AssetManager
 */
final class Bootstrap5IconsAsset extends AssetBundle
{
    public bool $cdn = true;

    /** @var string[]|CssFile[] */
    public array $css = [
        'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css',
    ];
}
