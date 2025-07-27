<?php

declare(strict_types=1);

namespace App\Infrastructure\Presentation\Web\Asset;

use Yiisoft\Assets\AssetBundle;
use Yiisoft\Assets\AssetManager;
use Yiisoft\Bootstrap5\Assets\BootstrapAsset;

/**
 * @psalm-import-type JsFile from AssetManager
 * @psalm-import-type CssFile from AssetManager
 */
final class AppAsset extends AssetBundle
{
    public ?string $basePath = '@assets';

    public ?string $baseUrl = '@assetsUrl';

    public ?string $sourcePath = '@resources/asset';

    /** @var string[]|CssFile[] */
    public array $css = [
        'css/site.css',
    ];

    /** @var string[]|JsFile[] */
    public array $js = [
        'js/app.js',
    ];

    public array $depends = [
        BootstrapAsset::class,
        Bootstrap5IconsAsset::class,
    ];
}
