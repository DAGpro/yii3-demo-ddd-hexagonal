<?php

declare(strict_types=1);

use App\Site\Presentation\Frontend\Web\Contact\ContactController;
use App\Site\Presentation\Frontend\Web\SiteController;
use Yiisoft\DataResponse\Middleware\FormatDataResponseAsHtml;
use Yiisoft\DataResponse\Middleware\FormatDataResponseAsJson;
use Yiisoft\Http\Method;
use Yiisoft\Router\Group;
use Yiisoft\Router\Route;
use Yiisoft\Swagger\Action\SwaggerJson;
use Yiisoft\Swagger\Action\SwaggerUi;

return [
    Route::get('/')
        ->action([SiteController::class, 'index'])
        ->name('site/index'),

    Route::post('/locale')
        ->action([SiteController::class, 'setLocale'])
        ->name('site/set-locale'),

    Route::methods([Method::GET, Method::POST], '/contact')
        ->action([ContactController::class, 'contact'])
        ->name('site/contact'),

    Group::create('/swagger')
        ->routes(
            Route::get('')
                ->name('swagger/index')
                ->middleware(FormatDataResponseAsHtml::class)
                ->action(fn(SwaggerUi $swaggerUi) => $swaggerUi->withJsonUrl('/swagger/json-url')),
            Route::get('/json-url')
                ->middleware(FormatDataResponseAsJson::class)
                ->action(SwaggerJson::class),
        ),
];
