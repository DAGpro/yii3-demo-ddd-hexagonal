<?php

declare(strict_types=1);

use App\Blog\Application\Service\QueryService\ReadPostQueryServiceInterface;
use App\Blog\Presentation\Frontend\Web\Archive\ArchiveController;
use App\Blog\Presentation\Frontend\Web\Author\AuthorPostController;
use App\Blog\Presentation\Frontend\Web\BlogController;
use App\Blog\Presentation\Frontend\Web\Comment\CommentController;
use App\Blog\Presentation\Frontend\Web\Post\PostController;
use App\Blog\Presentation\Frontend\Web\Tag\TagController;
use App\Infrastructure\Presentation\Web\Middleware\AccessPermissionChecker;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Auth\Middleware\Authentication;
use Yiisoft\Http\Method;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\Group;
use Yiisoft\Router\Route;
use Yiisoft\Yii\Middleware\HttpCache;

return [
    // Blog routes
    Group::create('/blog')
        ->routes(
        // Index
            Route::get('[/page/{page:\d+}]')
                ->name('blog/index')
//                ->middleware(
//                    fn(HttpCache $httpCache, ReadPostQueryServiceInterface $readPostQueryService)
//                        => $httpCache->withLastModified(function (ServerRequestInterface $request, $params) use (
//                        $readPostQueryService,
//                    ) {
//                        return $readPostQueryService->getMaxUpdatedAt()->getTimestamp();
//                    }),
//                )
                ->action([BlogController::class, 'index']),
            // Post page
            Route::get('/post/{slug}')
                ->name('blog/post')
                ->middleware(
                    fn(
                        HttpCache $httpCache,
                        ReadPostQueryServiceInterface $readPostQueryService,
                        CurrentRoute $currentRoute,
                    )
                        => $httpCache->withEtagSeed(function (ServerRequestInterface $request, $params) use (
                        $readPostQueryService,
                        $currentRoute,
                    ) {
                        $post = $readPostQueryService->getPostBySlug($currentRoute->getArgument('slug'));
                        return $post === null ? 'no-post' : $post->getSlug() . '-' . $post->getUpdatedAt(
                            )->getTimestamp();
                    }),
                )
                ->action([PostController::class, 'index']),

            // Tag page
            Route::get('/tag/{label}[/page/{page:\d+}]')
                ->name('blog/tag')
                ->action([TagController::class, 'index']),

            // Archive
            Group::create('/archive')
                ->routes(
                // Index page
                    Route::get('[/page/{page:\d+}]')
                        ->name('blog/archive/index')
                        ->action([ArchiveController::class, 'index']),
                    // Yearly page
                    Route::get('/year/{year:\d+}/[/page/{page:\d+}]')
                        ->name('blog/archive/year')
                        ->action([ArchiveController::class, 'yearlyArchive']),
                    // Monthly page
                    Route::get('/{year:\d+}-{month:\d+}[/page/{page:\d+}]')
                        ->name('blog/archive/month')
                        ->action([ArchiveController::class, 'monthlyArchive']),
                ),

            Group::create('/author')
                ->routes(
                // List author post page
                    Route::get('/{author}/posts[/page/{page}]')
                        ->name('blog/author/posts')
                        ->middleware(fn(AccessPermissionChecker $checker)
                            => $checker->withPermission('authorPostsList',
                        ),
                        )
                        ->middleware(Authentication::class)
                        ->action([AuthorPostController::class, 'authorPosts']),
                    // Author View Post page
                    Route::get('/post/view/{slug}')
                        ->name('blog/author/post/view')
                        ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('authorViewPost'))
                        ->middleware(Authentication::class)
                        ->action([AuthorPostController::class, 'view']),
                    // Add Post page
                    Route::methods([Method::GET, Method::POST], '/post/add')
                        ->name('blog/author/post/add')
                        ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('authorAddPost'))
                        ->middleware(Authentication::class)
                        ->action([AuthorPostController::class, 'add']),
                    // Edit Post page
                    Route::methods([Method::GET, Method::POST], '/post/edit/{slug}')
                        ->name('blog/author/post/edit')
                        ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('authorEditPost'))
                        ->middleware(Authentication::class)
                        ->action([AuthorPostController::class, 'edit']),
                    //Delete post page
                    Route::post('/post/delete/{slug}')
                        ->name('blog/author/post/delete')
                        ->middleware(fn(AccessPermissionChecker $checker)
                            => $checker->withPermission('authorDeletePost',
                        ))
                        ->middleware(Authentication::class)
                        ->action([AuthorPostController::class, 'delete']),
                ),

            // comments
            Route::get('/comment[/next/{next}]')
                ->name('blog/comment/index')
                ->action([CommentController::class, 'index']),
            //Add comments
            Route::methods([Method::GET, Method::POST], '/post/{slug}/comment/add')
                ->name('blog/comment/add')
                ->middleware(Authentication::class)
                ->action([CommentController::class, 'add']),
            //Edit comments
            Route::methods([Method::GET, Method::POST], '/post/comment/edit/{comment_id}')
                ->name('blog/comment/edit')
                ->middleware(Authentication::class)
                ->action([CommentController::class, 'edit']),
            //delete comments
            Route::post('/post/comment/delete')
                ->name('blog/comment/delete')
                ->middleware(Authentication::class)
                ->action([CommentController::class, 'delete']),
        ),
];
