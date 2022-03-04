<?php

declare(strict_types=1);

use App\Infrastructure\Presentation\Web\Middleware\AccessPermissionChecker;
use App\Site\Presentation\Backend\Web\SiteController;
use Yiisoft\Auth\Middleware\Authentication;
use Yiisoft\Router\Group;
use Yiisoft\Router\Route;

return [
    //Dashboard
    Group::create('/backend')
    ->routes(
        Route::get('')
            ->name('backend/dashboard')
            ->middleware(fn (AccessPermissionChecker $checker) => $checker->withPermission('indexDashboard'))
            ->middleware(Authentication::class)
            ->action([SiteController::class, 'index']),
    )
];
