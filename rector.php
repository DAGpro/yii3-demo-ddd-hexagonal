<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withPhpSets(php84: true)
    ->withParallel()
    ->withCache(
        cacheDirectory: __DIR__ . '/runtime/rector',
    )
    ->withSkip([
        __DIR__ . '/vendor',
        __DIR__ . '/runtime',
    ]);
