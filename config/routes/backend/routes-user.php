<?php

declare(strict_types=1);

use App\IdentityAccess\ContextMap\Middleware\AccessRoleChecker;
use App\IdentityAccess\Presentation\Backend\Web\User\CreateUserController;
use App\IdentityAccess\Presentation\Backend\Web\User\DeleteUserController;
use App\IdentityAccess\Presentation\Backend\Web\User\UserController;
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

                    Route::get('/[page/{page:\d+}]')
                        ->name('backend/user')
                        ->middleware(Authentication::class)
                        ->middleware(fn(AccessRoleChecker $checker) => $checker->withRole('admin'))
                        ->action([UserController::class, 'index']),

                    Route::get('/profile/{user_id}')
                        ->name('backend/user/profile')
                        ->middleware(Authentication::class)
                        ->middleware(fn(AccessRoleChecker $checker) => $checker->withRole('admin'))
                        ->action([UserController::class, 'profile']),

                    Route::methods([Method::GET, Method::POST], '/create')
                        ->name('backend/user/create')
                        ->middleware(Authentication::class)
                        ->middleware(fn(AccessRoleChecker $checker) => $checker->withRole('admin'))
                        ->action([CreateUserController::class, 'create']),

                    Route::post('/delete')
                        ->name('backend/user/delete')
                        ->middleware(Authentication::class)
                        ->middleware(fn(AccessRoleChecker $checker) => $checker->withRole('admin'))
                        ->action([DeleteUserController::class, 'remove']),

                    Route::post('/clear-users')
                        ->name('backend/user/clear-users')
                        ->middleware(Authentication::class)
                        ->middleware(fn(AccessRoleChecker $checker) => $checker->withRole('admin'))
                        ->action([UserController::class, 'clearUsers']),
                ),
        ),
];
