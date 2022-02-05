<?php

declare(strict_types=1);

namespace App\Presentation\Frontend\Web\Component\Blog\Author;

use App\Core\Component\Blog\Application\Service\CommandService\AuthorPostServiceInterface;
use App\Core\Component\Blog\Application\Service\CommandService\PostChangeDTO;
use App\Core\Component\Blog\Application\Service\CommandService\PostCreateDTO;
use App\Core\Component\Blog\Application\Service\QueryService\AuthorPostQueryServiceInterface;
use App\Core\Component\Blog\Domain\Exception\BlogNotFoundException;
use App\Core\Component\Blog\Infrastructure\Services\IdentityAccessService;
use App\Presentation\Infrastructure\Web\Service\WebControllerService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Yiisoft\Data\Paginator\OffsetPaginator;
use Yiisoft\Http\Method;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Validator\ValidatorInterface;
use Yiisoft\Yii\View\ViewRenderer;

final class AuthorPostController
{
    private const POSTS_PER_PAGE = 4;

    private ViewRenderer $view;
    private WebControllerService $webService;
    private AuthorPostServiceInterface $postService;
    private IdentityAccessService $identityAccessService;

    public function __construct(
        ViewRenderer $viewRenderer,
        WebControllerService $webService,
        AuthorPostServiceInterface $postService,
        IdentityAccessService $identityAccessService
    ) {
        $this->view = $viewRenderer->withControllerName('component/blog/author');
        $this->webService = $webService;
        $this->postService = $postService;
        $this->identityAccessService = $identityAccessService;
    }

    public function authorPosts(
        CurrentRoute $currentRoute,
        AuthorPostQueryServiceInterface $postQueryService
    ): Response {
        $pageNum = (int)$currentRoute->getArgument('page', '1');
        if (!($author = $this->identityAccessService->getAuthor())) {
            return $this->webService->accessDenied();
        }

        $dataReader = $postQueryService->getAuthorPosts($author);

        $paginator = (new OffsetPaginator($dataReader))
            ->withPageSize(self::POSTS_PER_PAGE)
            ->withCurrentPage($pageNum);

        return $this->view->render('posts', ['paginator' => $paginator, 'author' => $author]);
    }

    public function view(
        CurrentRoute $currentRoute,
        AuthorPostQueryServiceInterface $postQueryService
    ): Response {
        $slug = $currentRoute->getArgument('slug', '');

        $post = $postQueryService->getPostBySlug($slug);
        if ($post === null) {
            return $this->webService->notFound();
        }

        if (!$this->identityAccessService->isAuthor($post)) {
            return $this->webService->accessDenied();
        }

        return $this->view->render('post', ['post' => $post]);
    }

    public function add(Request $request, ValidatorInterface $validator): Response
    {
        if (!($author = $this->identityAccessService->getAuthor())) {
            return $this->webService->accessDenied();
        }

        $form = new PostForm(null);
        if ($request->getMethod() === Method::POST
            && $form->load($request->getParsedBody())
            && $validator->validate($form)->isValid()
        ) {
            $this->postService->create(
                new PostCreateDTO(
                    $form->getTitle(),
                    $form->getContent(),
                    $form->getTags()
                ),
                $author
            );

            return $this->webService->redirect('blog/author/posts', ['author' => $author->getName()]);
        }

        return $this->view->render('form_post',
            [
                'title' => 'Add post',
                'action' => ['blog/author/post/add'],
                'form' => $form,
            ]
        );
    }

    public function edit(
        Request $request,
        CurrentRoute $currentRoute,
        AuthorPostQueryServiceInterface $postQueryService,
        ValidatorInterface $validator
    ): Response {
        $slug = $currentRoute->getArgument('slug', '');

        if (!($post = $postQueryService->getPostBySlug($slug))) {
            return $this->webService->notFound();
        }

        if (!$this->identityAccessService->isAuthor($post)) {
            return $this->webService->accessDenied();
        }

        $form = new PostForm($post);
        if ($request->getMethod() === Method::POST
            && $form->load($request->getParsedBody())
            && $validator->validate($form)->isValid()
        ) {
            try {
                $this->postService->edit(
                    $post->getSlug(),
                    new PostChangeDTO(
                        $form->getTitle(), $form->getContent(), $form->getTags()
                    )
                );
            } catch (BlogNotFoundException $exception) {
                return $this->webService->notFound();
            }

            return $this->webService->redirect('blog/author/post/view', ['slug' => $post->getSlug()]);
        }

        return $this->view->render('form_post',
            [
            'title' => 'Edit post',
            'action' => ['blog/author/post/edit', ['slug' => $slug]],
            'form' => $form,
        ]);
    }

    //remove?
    public function delete(
        CurrentRoute $currentRoute,
        AuthorPostQueryServiceInterface $postQueryService
    ): Response {
        $slug = $currentRoute->getArgument('slug', '');

        if (!($post = $postQueryService->getPostBySlug($slug))) {
            return $this->webService->notFound();
        }

        if (!$this->identityAccessService->isAuthor($post)) {
            return $this->webService->accessDenied();
        }

        try {
            $this->postService->delete($slug);
        } catch (BlogNotFoundException $exception) {
            return $this->webService->notFound();
        }

        return $this->webService->redirect('blog');
    }
}
