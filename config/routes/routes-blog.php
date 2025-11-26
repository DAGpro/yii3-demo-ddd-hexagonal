<?php

declare(strict_types=1);

use App\Blog\Slice\Blog\FrontController\BlogController;
use App\Blog\Slice\Comment\Presentation\Frontend\Web\CommentController;
use App\Blog\Slice\Post\Controller\Frontend\Web\Archive\ArchiveController;
use App\Blog\Slice\Post\Controller\Frontend\Web\Author\AuthorPostController;
use App\Blog\Slice\Post\Controller\Frontend\Web\Post\PostController;
use App\Blog\Slice\Post\Service\QueryService\ReadPostQueryServiceInterface;
use App\Blog\Slice\Tag\FrontController\Web\TagController;
use App\IdentityAccess\ContextMap\Middleware\AccessPermissionChecker;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Auth\Middleware\Authentication;
use Yiisoft\Http\Method;
use Yiisoft\HttpMiddleware\HttpCache\ETag;
use Yiisoft\HttpMiddleware\HttpCache\ETagProvider\ETagProviderInterface;
use Yiisoft\HttpMiddleware\HttpCache\HttpCacheMiddleware;
use Yiisoft\HttpMiddleware\HttpCache\LastModifiedProvider\LastModifiedProviderInterface;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\Group;
use Yiisoft\Router\Route;

return [
    Group::create('/blog')
        ->routes(
            Route::get('[/page/{page:\d+}]')
                ->name('blog/index')
                ->middleware(
                    fn(
                        ResponseFactoryInterface $responseFactory,
                        ReadPostQueryServiceInterface $readPostQueryService,
                    )
                        => new HttpCacheMiddleware(
                            $responseFactory,
                            lastModifiedProvider: new readonly class ($readPostQueryService) implements
                                LastModifiedProviderInterface {
                                public function __construct(
                                    private ReadPostQueryServiceInterface $readPostQueryService,
                                ) {}

                                #[Override]
                                public function get(ServerRequestInterface $request): DateTimeImmutable
                                {
                                    return $this->readPostQueryService->getMaxUpdatedAt();
                                }
                            },
                        ),
                )
                ->action([BlogController::class, 'index']),
            Route::get('/post/{slug}')
                ->name('blog/post')
                ->middleware(
                    fn(
                        ResponseFactoryInterface $responseFactory,
                        ReadPostQueryServiceInterface $readPostQueryService,
                        CurrentRoute $currentRoute,
                    )
                        => new HttpCacheMiddleware(
                            $responseFactory,
                            eTagProvider: new readonly class ($readPostQueryService, $currentRoute) implements
                                ETagProviderInterface {
                                public function __construct(
                                    private ReadPostQueryServiceInterface $readPostQueryService,
                                    private CurrentRoute $currentRoute,
                                ) {}

                                #[Override]
                                public function get(ServerRequestInterface $request): ?ETag
                                {
                                    $post = $this->readPostQueryService->getPostBySlug(
                                        $this->currentRoute->getArgument('slug') ?? '',
                                    );

                                    return $post ? new ETag(
                                        $post->getSlug() . '-' . $post->getUpdatedAt()->getTimestamp(),
                                    ) : null;
                                }
                            },
                        ),
                )
                ->action([PostController::class, 'index']),
            Route::get('/tag/{label}[/page/{page:\d+}]')
                ->name('blog/tag')
                ->action([TagController::class, 'index']),
            Group::create('/archive')
                ->routes(
                    Route::get('[/page/{page:\d+}]')
                        ->name('blog/archive/index')
                        ->action([ArchiveController::class, 'index']),
                    Route::get('/year/{year:\d+}/[/page/{page:\d+}]')
                        ->name('blog/archive/year')
                        ->action([ArchiveController::class, 'yearlyArchive']),
                    Route::get('/{year:\d+}-{month:\d+}[/page/{page:\d+}]')
                        ->name('blog/archive/month')
                        ->action([ArchiveController::class, 'monthlyArchive']),
                ),
            Group::create('/author')
                ->routes(
                    Route::get('/{author}/posts[/page/{page}]')
                        ->name('blog/author/posts')
                        ->middleware(
                            fn(AccessPermissionChecker $checker) => $checker->withPermission('authorPostsList'),
                        )
                        ->middleware(Authentication::class)
                        ->action([AuthorPostController::class, 'authorPosts']),
                    Route::get('/post/view/{slug}')
                        ->name('blog/author/post/view')
                        ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('authorViewPost'))
                        ->middleware(Authentication::class)
                        ->action([AuthorPostController::class, 'view']),
                    Route::methods([Method::GET, Method::POST], '/post/add')
                        ->name('blog/author/post/add')
                        ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('authorAddPost'))
                        ->middleware(Authentication::class)
                        ->action([AuthorPostController::class, 'add']),
                    Route::methods([Method::GET, Method::POST], '/post/edit/{slug}')
                        ->name('blog/author/post/edit')
                        ->middleware(fn(AccessPermissionChecker $checker) => $checker->withPermission('authorEditPost'))
                        ->middleware(Authentication::class)
                        ->action([AuthorPostController::class, 'edit']),
                    Route::post('/post/delete/{slug}')
                        ->name('blog/author/post/delete')
                        ->middleware(
                            fn(AccessPermissionChecker $checker) => $checker->withPermission('authorDeletePost'),
                        )
                        ->middleware(Authentication::class)
                        ->action([AuthorPostController::class, 'delete']),
                ),
            Route::get('/comment[/next/{next}]')
                ->name('blog/comment/index')
                ->action([CommentController::class, 'index']),
            Route::methods([Method::GET, Method::POST], '/post/{slug}/comment/add')
                ->name('blog/comment/add')
                ->middleware(Authentication::class)
                ->action([CommentController::class, 'add']),
            Route::methods([Method::GET, Method::POST], '/post/comment/edit/{comment_id}')
                ->name('blog/comment/edit')
                ->middleware(Authentication::class)
                ->action([CommentController::class, 'edit']),
            Route::post('/post/comment/delete')
                ->name('blog/comment/delete')
                ->middleware(Authentication::class)
                ->action([CommentController::class, 'delete']),
        ),
];
