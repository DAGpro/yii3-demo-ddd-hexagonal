<?php

declare(strict_types=1);

use App\Blog\Presentation\Backend\Web\CommentController;
use App\Blog\Presentation\Backend\Web\PostController;
use App\Blog\Presentation\Backend\Web\TagController;
use App\Infrastructure\Presentation\Web\Middleware\AccessPermissionChecker;
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
                    Route::get('/[page-{page:\d+}]')
                        ->name('backend/post')
                        ->middleware(fn (AccessPermissionChecker $checker) => $checker->withPermission('indexPost'))
                        ->middleware(Authentication::class)
                        ->action([PostController::class, 'index']),
                    // View Post page
                    Route::get('/view/{post_id}')
                        ->name('backend/post/view')
                        ->middleware(fn (AccessPermissionChecker $checker) => $checker->withPermission('viewPost'))
                        ->middleware(Authentication::class)
                        ->action([PostController::class, 'view']),
                    // Draft Post page
                    Route::post('/draft/{post_id}')
                        ->name('backend/post/draft')
                        ->middleware(fn (AccessPermissionChecker $checker) => $checker->withPermission('draftPost'))
                        ->middleware(Authentication::class)
                        ->action([PostController::class, 'draftPost']),
                    //Public post page
                    Route::post('/public/{post_id}')
                        ->name('backend/post/public')
                        ->middleware(fn (AccessPermissionChecker $checker) => $checker->withPermission('publicPost'))
                        ->middleware(Authentication::class)
                        ->action([PostController::class, 'publicPost']),
                    //Moderate post page
                    Route::methods([Method::GET, Method::POST], '/moderate/{post_id}')
                        ->name('backend/post/moderate')
                        ->middleware(fn (AccessPermissionChecker $checker) => $checker->withPermission('moderatePost'))
                        ->middleware(Authentication::class)
                        ->action([PostController::class, 'moderate']),
                    //Delete post page
                    Route::post( '/delete/{post_id}')
                        ->name('backend/post/delete')
                        ->middleware(fn (AccessPermissionChecker $checker) => $checker->withPermission('deletePost'))
                        ->middleware(Authentication::class)
                        ->action([PostController::class, 'delete']),
                ),


            // Tags page
            Route::get('/tag[/page-{page:\d+}]')
                ->name('backend/tag')
                ->middleware(fn (AccessPermissionChecker $checker) => $checker->withPermission('indexTag'))
                ->middleware(Authentication::class)
                ->action([TagController::class, 'index']),
            //Change Tag page
            Route::methods([Method::GET, Method::POST],'/tag/change[/{tag_id:\d+}]')
                ->name('backend/tag/change')
                ->middleware(fn (AccessPermissionChecker $checker) => $checker->withPermission('changeTag'))
                ->middleware(Authentication::class)
                ->action([TagController::class, 'changeTag']),
            //Delete Tag page
            Route::post('/tag/delete/{tag_id}')
                ->name('backend/tag/delete')
                ->middleware(fn (AccessPermissionChecker $checker) => $checker->withPermission('deleteTag'))
                ->middleware(Authentication::class)
                ->action([TagController::class, 'delete']),

            Group::create('/comment')
                ->routes(
                // comments
                    Route::get('/[page/{page}]')
                        ->name('backend/comment')
                        ->middleware(fn (AccessPermissionChecker $checker) => $checker->withPermission('indexComment'))
                        ->middleware(Authentication::class)
                        ->action([CommentController::class, 'index']),
                    //View comments
                    Route::get('/view/{comment_id}')
                        ->name('backend/comment/view')
                        ->middleware(fn (AccessPermissionChecker $checker) => $checker->withPermission('viewComment'))
                        ->middleware(Authentication::class)
                        ->action([CommentController::class, 'view']),
                    //Draft comments
                    Route::post('/draft/{comment_id}')
                        ->name('backend/comment/draft')
                        ->middleware(fn (AccessPermissionChecker $checker) => $checker->withPermission('draftComment'))
                        ->middleware(Authentication::class)
                        ->action([CommentController::class, 'draftComment']),
                    //Public comments
                    Route::post('/public/{comment_id}')
                        ->name('backend/comment/public')
                        ->middleware(fn (AccessPermissionChecker $checker) => $checker->withPermission('publicComment'))
                        ->middleware(Authentication::class)
                        ->action([CommentController::class, 'publicComment']),
                    //Moderate comments
                    Route::methods([Method::GET, Method::POST], '/moderate/{comment_id}')
                        ->name('backend/comment/moderate')
                        ->middleware(fn (AccessPermissionChecker $checker) => $checker->withPermission('moderateComment'))
                        ->middleware(Authentication::class)
                        ->action([CommentController::class, 'moderateComment']),
                    //Delete comments
                    Route::post('/delete[/{comment_id:\d+}]')
                        ->name('backend/comment/delete')
                        ->middleware(fn (AccessPermissionChecker $checker) => $checker->withPermission('deleteComment'))
                        ->middleware(Authentication::class)
                        ->action([CommentController::class, 'delete']),
                ),

        ),
];

