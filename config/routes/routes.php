<?php

declare(strict_types=1);

use App\Presentation\Frontend\Web\Controller\SiteController;
use App\Presentation\Frontend\Web\Controller\Contact\ContactController;
use Yiisoft\Http\Method;
use Yiisoft\Router\Route;

return [
    // Lonely pages of site
    Route::get('/')
        ->action([SiteController::class, 'index'])
        ->name('site/index'),
    Route::post('/locale')
        ->action([SiteController::class, 'setLocale'])
        ->name('site/set-locale'),
    Route::methods([Method::GET, Method::POST], '/contact')
        ->action([ContactController::class, 'contact'])
        ->name('site/contact'),
];
