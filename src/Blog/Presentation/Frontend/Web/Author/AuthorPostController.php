<?php

declare(strict_types=1);

namespace App\Blog\Presentation\Frontend\Web\Author;

use App\Blog\Application\Service\CommandService\AuthorPostServiceInterface;
use App\Blog\Application\Service\CommandService\PostChangeDTO;
use App\Blog\Application\Service\CommandService\PostCreateDTO;
use App\Blog\Application\Service\QueryService\AuthorPostQueryServiceInterface;
use App\Blog\Domain\Exception\BlogAccessDeniedException;
use App\Blog\Domain\Exception\BlogNotFoundException;
use App\Blog\Infrastructure\Services\IdentityAccessService;
use App\Infrastructure\Presentation\Web\Service\WebControllerService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Yiisoft\Data\Paginator\OffsetPaginator;
use Yiisoft\FormModel\FormHydrator;
use Yiisoft\Http\Method;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Yii\View\Renderer\ViewRenderer;

final readonly class AuthorPostController
{
    private const int POSTS_PER_PAGE = 4;

    private ViewRenderer $view;

    public function __construct(
        ViewRenderer $viewRenderer,
        private WebControllerService $webService,
        private AuthorPostServiceInterface $postService,
        private IdentityAccessService $identityAccessService,
    ) {
        $this->view = $viewRenderer->withViewPath('@blogView/author');
    }

    public function authorPosts(
        CurrentRoute $currentRoute,
        AuthorPostQueryServiceInterface $postQueryService,
    ): Response {
        $pageNum = max(1, (int) $currentRoute->getArgument('page', '1'));
        if (!($author = $this->identityAccessService->getAuthor())) {
            return $this->webService->accessDenied();
        }

        $dataReader = $postQueryService->getAuthorPosts($author);

        $paginator = new OffsetPaginator($dataReader)
            ->withPageSize(self::POSTS_PER_PAGE)
            ->withCurrentPage($pageNum);

        return $this->view->render('posts', ['paginator' => $paginator, 'author' => $author]);
    }

    public function view(
        CurrentRoute $currentRoute,
        AuthorPostQueryServiceInterface $postQueryService,
    ): Response {
        $slug = $currentRoute->getArgument('slug');
        if ($slug === null) {
            return $this->webService->notFound();
        }

        $post = $postQueryService->getPostBySlug($slug);
        if ($post === null) {
            return $this->webService->notFound();
        }

        if (!$this->identityAccessService->isAuthor($post)) {
            return $this->webService->accessDenied();
        }

        return $this->view->render('post', ['post' => $post]);
    }

    /**
     * @throws BlogNotFoundException
     */
    public function add(
        Request $request,
        PostForm $form,
        FormHydrator $formHydrator,
    ): Response {
        if (!($author = $this->identityAccessService->getAuthor())) {
            return $this->webService->accessDenied();
        }

        if ($request->getMethod() === Method::POST
            && $formHydrator->populateFromPostAndValidate($form, $request)
        ) {
            $this->postService->create(
                new PostCreateDTO(
                    $form->getTitle(),
                    $form->getContent(),
                    $form->getTags(),
                ),
                $author,
            );

            return $this->webService->redirect('blog/author/posts', ['author' => $author->getName()]);
        }

        return $this->view->render(
            'form_post',
            [
                'title' => 'Add post',
                'action' => ['route' => 'blog/author/post/add'],
                'form' => $form,
            ],
        );
    }

    /**
     * @throws BlogNotFoundException
     */
    public function edit(
        Request $request,
        CurrentRoute $currentRoute,
        AuthorPostQueryServiceInterface $postQueryService,
        formHydrator $formHydrator,
    ): Response {
        $slug = $currentRoute->getArgument('slug');
        if ($slug === null) {
            return $this->webService->notFound();
        }

        $post = $postQueryService->getPostBySlug($slug);
        if ($post === null) {
            return $this->webService->notFound();
        }

        $author = $this->identityAccessService->getAuthor();
        if ($author === null || !$post->isAuthor($author)) {
            return $this->webService->accessDenied();
        }

        $form = new PostForm($post);
        if ($request->getMethod() === Method::POST
            && $formHydrator->populateFromPostAndValidate($form, $request)
        ) {
            try {
                $this->postService->edit(
                    $post->getSlug(),
                    new PostChangeDTO(
                        $form->getTitle(),
                        $form->getContent(),
                        $form->getTags(),
                    ),
                    $author,
                );
            } catch (BlogAccessDeniedException $e) {
                return $this->webService->accessDenied($e->getMessage());
            }

            return $this->webService->redirect('blog/author/post/view', ['slug' => $post->getSlug()]);
        }

        return $this->view->render(
            'form_post',
            [
                'title' => 'Edit post',
                'action' => ['route' => 'blog/author/post/edit', 'arguments' => ['slug' => $slug]],
                'form' => $form,
            ],
        );
    }

    /**
     * @throws BlogNotFoundException
     */
    public function delete(
        CurrentRoute $currentRoute,
        AuthorPostQueryServiceInterface $postQueryService,
    ): Response {
        $slug = $currentRoute->getArgument('slug');
        if ($slug === null) {
            return $this->webService->notFound();
        }

        $post = $postQueryService->getPostBySlug($slug);
        if ($post === null) {
            return $this->webService->notFound();
        }

        $author = $this->identityAccessService->getAuthor();
        if ($author === null || !$post->isAuthor($author)) {
            return $this->webService->accessDenied();
        }

        try {
            $this->postService->delete($slug, $author);
        } catch (BlogAccessDeniedException $exception) {
            return $this->webService->accessDenied(
                $exception->getMessage(),
            );
        }

        return $this->webService->redirect('blog');
    }
}
