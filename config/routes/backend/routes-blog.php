<?php

declare(strict_types=1);

use App\Blog\Slice\Comment\Presentation\Backend\Web\CommentController;
use App\Blog\Slice\Post\Controller\Backend\Web\PostController;
use App\Blog\Slice\Tag\BackController\Web\TagController;
use App\IdentityAccess\ContextMap\Middleware\AccessPermissionChecker;
use Yiisoft\Auth\Middleware\Authentication;
use Yiisoft\Http\Method;
use Yiisoft\Router\Group;
use Yiisoft\Router\Route;

return [
    Group::create('/backend')
        ->routes(
            Group::create('/post')
                ->routes(
                    Route::get('/[page/{page:\d+}]')
                        ->name('backend/post')
                        ->middleware(Authentication::class)
                        ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('indexPost'))
                        ->action([PostController::class, 'index']),
                    Route::get('/view/{post_id}')
                        ->name('backend/post/view')
                        ->middleware(Authentication::class)
                        ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('viewPost'))
                        ->action([PostController::class, 'view']),
                    Route::post('/draft/{post_id}')
                        ->name('backend/post/draft')
                        ->middleware(Authentication::class)
                        ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('draftPost'))
                        ->action([PostController::class, 'draftPost']),
                    Route::post('/public/{post_id}')
                        ->name('backend/post/public')
                        ->middleware(Authentication::class)
                        ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('publicPost'))
                        ->action([PostController::class, 'publicPost']),
                    Route::methods([Method::GET, Method::POST], '/moderate/{post_id}')
                        ->name('backend/post/moderate')
                        ->middleware(Authentication::class)
                        ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('moderatePost'))
                        ->action([PostController::class, 'moderate']),
                    Route::post('/delete/{post_id}')
                        ->name('backend/post/delete')
                        ->middleware(Authentication::class)
                        ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('deletePost'))
                        ->action([PostController::class, 'delete']),
                ),
            Route::get('/tag[/page/{page:\d+}]')
                ->name('backend/tag')
                ->middleware(Authentication::class)
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('indexTag'))
                ->action([TagController::class, 'index']),
            Route::methods([Method::GET, Method::POST], '/tag/change[/{tag_id:\d+}]')
                ->name('backend/tag/change')
                ->middleware(Authentication::class)
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('changeTag'))
                ->action([TagController::class, 'changeTag']),
            Route::post('/tag/delete/{tag_id}')
                ->name('backend/tag/delete')
                ->middleware(Authentication::class)
                ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('deleteTag'))
                ->action([TagController::class, 'delete']),
            Group::create('/comment')
                ->routes(
                    Route::get('/[page/{page}]')
                        ->name('backend/comment')
                        ->middleware(Authentication::class)
                        ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('indexComment'))
                        ->action([CommentController::class, 'index']),
                    Route::get('/view/{comment_id}')
                        ->name('backend/comment/view')
                        ->middleware(Authentication::class)
                        ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('viewComment'))
                        ->action([CommentController::class, 'view']),
                    Route::post('/draft/{comment_id}')
                        ->name('backend/comment/draft')
                        ->middleware(Authentication::class)
                        ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('draftComment'))
                        ->action([CommentController::class, 'draftComment']),
                    Route::post('/public/{comment_id}')
                        ->name('backend/comment/public')
                        ->middleware(Authentication::class)
                        ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('publicComment'))
                        ->action([CommentController::class, 'publicComment']),
                    Route::methods([Method::GET, Method::POST], '/moderate/{comment_id}')
                        ->name('backend/comment/moderate')
                        ->middleware(Authentication::class)
                        ->middleware(
                            fn(AccessPermissionChecker $checker)
                                => $checker->withPermission(
                                    'moderateComment',
                                ),
                        )
                        ->action([CommentController::class, 'moderateComment']),
                    Route::post('/delete[/{comment_id:\d+}]')
                        ->name('backend/comment/delete')
                        ->middleware(Authentication::class)
                        ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('deleteComment'))
                        ->action([CommentController::class, 'delete']),
                ),
        ),
];
