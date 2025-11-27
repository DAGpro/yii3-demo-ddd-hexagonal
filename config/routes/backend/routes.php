<?php

declare(strict_types=1);

use App\IdentityAccess\ContextMap\Middleware\AccessPermissionChecker;
use App\Site\Presentation\Backend\Web\SiteController;
use Yiisoft\Auth\Middleware\Authentication;
use Yiisoft\Router\Group;
use Yiisoft\Router\Route;

return [
    Group::create('/backend')
        ->routes(
            Route::get('')
                ->name('backend/dashboard')
                ->middleware(Authentication::class)
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('indexDashboard'))
                ->action([SiteController::class, 'index']),
        ),
];
