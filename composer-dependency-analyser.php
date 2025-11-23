<?php

declare(strict_types=1);

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

$root = __DIR__;
return new Configuration()
    ->disableComposerAutoloadPathScan()
    ->setFileExtensions(['php'])
    ->addPathToScan($root . '/autoload.php', isDev: false)
    ->addPathToScan($root . '/src', isDev: false)
    ->addPathToScan($root . '/config', isDev: false)
    ->addPathToScan($root . '/public/index.php', isDev: false)
    ->addPathToScan($root . '/yii', isDev: false)
    ->addPathToScan($root . '/tests', isDev: true)
    ->ignoreUnknownClasses([
        'App\\Tests\\AcceptanceTester',
        'App\\Tests\\CliTester',
        'App\\Tests\\FunctionalTester',
        'App\\Tests\\UnitTester',
    ])
    ->ignoreErrorsOnPackages([
        'yiisoft/config',
        'yiisoft/di',
        'yiisoft/yii-event',
    ], [ErrorType::PROD_DEPENDENCY_ONLY_IN_DEV])
    ->ignoreErrorsOnPackages([
        'spiral/core',
        'cycle/annotated',
        'cycle/schema-builder',
        'cycle/schema-provider',
        'psr/event-dispatcher',
        'psr/simple-cache',
        'yiisoft/hydrator',
        'yiisoft/request-provider',
        'zircote/swagger-php',
    ], [ErrorType::SHADOW_DEPENDENCY]);
