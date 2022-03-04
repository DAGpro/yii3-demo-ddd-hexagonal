<?php

declare(strict_types=1);

use App\IdentityAccess\Presentation\Backend\Web\User\CreateUserController;
use App\IdentityAccess\Presentation\Backend\Web\User\DeleteUserController;
use App\IdentityAccess\Presentation\Backend\Web\User\UserController;
use App\Infrastructure\Presentation\Web\Middleware\AccessRoleChecker;
use Yiisoft\Auth\Middleware\Authentication;
use Yiisoft\Http\Method;
use Yiisoft\Router\Group;
use Yiisoft\Router\Route;

return [
    // User routes
    Group::create('/backend')
        ->routes(
            Group::create('/user')
            ->routes(

                Route::get( '/[page-{page:\d+}]')
                    ->name('backend/user')
                    ->middleware(fn (AccessRoleChecker $checker) => $checker->withRole('admin'))
                    ->middleware(Authentication::class)
                    ->action([UserController::class, 'index']),

                Route::get( '/profile/{user_id}')
                    ->name('backend/user/profile')
                    ->middleware(fn (AccessRoleChecker $checker) => $checker->withRole('admin'))
                    ->middleware(Authentication::class)
                    ->action([UserController::class, 'profile']),

                Route::methods([Method::GET, Method::POST], '/create')
                    ->name('backend/user/create')
                    ->middleware(fn (AccessRoleChecker $checker) => $checker->withRole('admin'))
                    ->middleware(Authentication::class)
                    ->action([CreateUserController::class, 'create']),

                Route::post( '/delete')
                    ->name('backend/user/delete')
                    ->middleware(fn (AccessRoleChecker $checker) => $checker->withRole('admin'))
                    ->middleware(Authentication::class)
                    ->action([DeleteUserController::class, 'remove']),

                Route::post( '/clear-users')
                    ->name('backend/user/clear-users')
                    ->middleware(fn (AccessRoleChecker $checker) => $checker->withRole('admin'))
                    ->middleware(Authentication::class)
                    ->action([UserController::class, 'clearUsers']),
            ),
        ),
];

