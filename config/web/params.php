<?php

declare(strict_types=1);

use Yiisoft\Cookies\CookieMiddleware;
use Yiisoft\ErrorHandler\Middleware\ErrorCatcher;
use Yiisoft\RequestProvider\RequestCatcherMiddleware;
use Yiisoft\Router\Middleware\Router;
use Yiisoft\Session\SessionMiddleware;
use Yiisoft\User\Login\Cookie\CookieLoginMiddleware;
use Yiisoft\Yii\Middleware\Locale;
use Yiisoft\Yii\Middleware\Subfolder;

return [
    'middlewares' => [
        RequestCatcherMiddleware::class,
        ErrorCatcher::class,
        SessionMiddleware::class,
        CookieMiddleware::class,
        CookieLoginMiddleware::class,
        Subfolder::class,
        Locale::class,
        Router::class,
    ],

    'locale' => [
        'locales' => [
            'en' => 'en-US',    // English
            'zh' => 'zh-CN',    // Chinese (simplified)
            'es' => 'es-ES',    // Spanish
            'hi' => 'hi-IN',    // Hindi
            'ar' => 'ar-SA',    // Arabic
            'pt' => 'pt-BR',    // Portuguese (Brazil)
            'ru' => 'ru-RU',    // Russian
        ],
        'ignoredRequests' => [
            '/gii**',
            '/debug**',
            '/inspect**',
        ],
    ],

    'yiisoft/widget' => [
        'defaultTheme' => 'bootstrap5',
    ],
];
