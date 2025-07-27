<?php

declare(strict_types=1);

use App\Blog\Presentation\Backend\Web\CommentController;
use App\Blog\Presentation\Backend\Web\PostController;
use App\Blog\Presentation\Backend\Web\TagController;
use App\IdentityAccess\Middleware\AccessPermissionChecker;
use Yiisoft\Auth\Middleware\Authentication;
use Yiisoft\Http\Method;
use Yiisoft\Router\Group;
use Yiisoft\Router\Route;

return [
    // Blog routes
    Group::create('/backend')
        ->routes(
            Group::create('/post')
                ->routes(
                // Post page
                    Route::get('/[page/{page:\d+}]')
                        ->name('backend/post')
                        ->middleware(Authentication::class)
                        ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('indexPost'))
                        ->action([PostController::class, 'index']),
                    // View Post page
                    Route::get('/view/{post_id}')
                        ->name('backend/post/view')
                        ->middleware(Authentication::class)
                        ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('viewPost'))
                        ->action([PostController::class, 'view']),
                    // Draft Post page
                    Route::post('/draft/{post_id}')
                        ->name('backend/post/draft')
                        ->middleware(Authentication::class)
                        ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('draftPost'))
                        ->action([PostController::class, 'draftPost']),
                    //Public post page
                    Route::post('/public/{post_id}')
                        ->name('backend/post/public')
                        ->middleware(Authentication::class)
                        ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('publicPost'))
                        ->action([PostController::class, 'publicPost']),
                    //Moderate post page
                    Route::methods([Method::GET, Method::POST], '/moderate/{post_id}')
                        ->name('backend/post/moderate')
                        ->middleware(Authentication::class)
                        ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('moderatePost'))
                        ->action([PostController::class, 'moderate']),
                    //Delete post page
                    Route::post('/delete/{post_id}')
                        ->name('backend/post/delete')
                        ->middleware(Authentication::class)
                        ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('deletePost'))
                        ->action([PostController::class, 'delete']),
                ),


            // Tags page
            Route::get('/tag[/page/{page:\d+}]')
                ->name('backend/tag')
                ->middleware(Authentication::class)
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('indexTag'))
                ->action([TagController::class, 'index']),
            //Change Tag page
            Route::methods([Method::GET, Method::POST], '/tag/change[/{tag_id:\d+}]')
                ->name('backend/tag/change')
                ->middleware(Authentication::class)
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('changeTag'))
                ->action([TagController::class, 'changeTag']),
            //Delete Tag page
            Route::post('/tag/delete/{tag_id}')
                ->name('backend/tag/delete')
                ->middleware(Authentication::class)
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('deleteTag'))
                ->action([TagController::class, 'delete']),

            Group::create('/comment')
                ->routes(
                // comments
                    Route::get('/[page/{page}]')
                        ->name('backend/comment')
                        ->middleware(Authentication::class)
                        ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('indexComment'))
                        ->action([CommentController::class, 'index']),
                    //View comments
                    Route::get('/view/{comment_id}')
                        ->name('backend/comment/view')
                        ->middleware(Authentication::class)
                        ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('viewComment'))
                        ->action([CommentController::class, 'view']),
                    //Draft comments
                    Route::post('/draft/{comment_id}')
                        ->name('backend/comment/draft')
                        ->middleware(Authentication::class)
                        ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('draftComment'))
                        ->action([CommentController::class, 'draftComment']),
                    //Public comments
                    Route::post('/public/{comment_id}')
                        ->name('backend/comment/public')
                        ->middleware(Authentication::class)
                        ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('publicComment'))
                        ->action([CommentController::class, 'publicComment']),
                    //Moderate comments
                    Route::methods([Method::GET, Method::POST], '/moderate/{comment_id}')
                        ->name('backend/comment/moderate')
                        ->middleware(Authentication::class)
                        ->middleware(fn(AccessPermissionChecker $checker)
                            => $checker->withPermission('moderateComment',
                        ),
                        )
                        ->action([CommentController::class, 'moderateComment']),
                    //Delete comments
                    Route::post('/delete[/{comment_id:\d+}]')
                        ->name('backend/comment/delete')
                        ->middleware(Authentication::class)
                        ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('deleteComment'))
                        ->action([CommentController::class, 'delete']),
                ),

        ),
];
