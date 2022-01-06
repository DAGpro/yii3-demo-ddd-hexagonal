<?php

declare(strict_types=1);

namespace App\Presentation\Frontend\Web\Component\Blog\Post;

use App\Core\Component\Blog\Application\PostService;
use App\Core\Component\Blog\Domain\Post;
use App\Core\Component\Blog\Infrastructure\Persistence\Post\PostRepository;
use App\Core\Component\IdentityAccess\User\Application\UserService;
use App\Infrastructure\Authentication\AuthenticationService;
use App\Presentation\Infrastructure\Web\Service\WebControllerService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Yiisoft\Http\Method;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Validator\ValidatorInterface;
use Yiisoft\Yii\View\ViewRenderer;

final class PostController
{
    private ViewRenderer $viewRenderer;
    private WebControllerService $webService;
    private PostService $postService;
    private AuthenticationService $authService;

    public function __construct(
        ViewRenderer $viewRenderer,
        WebControllerService $webService,
        PostService $postService,
        AuthenticationService $authenticationService
    ) {
        $this->viewRenderer = $viewRenderer->withControllerName('component/blog/post');
        $this->webService = $webService;
        $this->postService = $postService;
        $this->authService = $authenticationService;
    }

    public function index(CurrentRoute $currentRoute, UserService $userService, PostRepository $postRepository): Response
    {
        $canEdit = $userService->hasPermission('editPost');
        $slug = $currentRoute->getArgument('slug');
        $item = $postRepository->fullPostPage($slug);
        if ($item === null) {
            return $this->webService->getNotFoundResponse();
        }

        return $this->viewRenderer->render('index', ['item' => $item, 'canEdit' => $canEdit, 'slug' => $slug]);
    }

    public function add(Request $request, ValidatorInterface $validator): Response
    {
        $parameters = [
            'title' => 'Add post',
            'action' => ['blog/add'],
            'errors' => [],
            'body' => $request->getParsedBody(),
        ];

        if ($request->getMethod() === Method::POST) {
            $form = new PostForm();
            if ($form->load($parameters['body']) && $validator->validate($form)->isValid()) {
                $this->postService->savePost($this->authService->getUser(), new Post(), $form);
                return $this->webService->getRedirectResponse('blog/index');
            }

            $parameters['errors'] = $form->getFormErrors()->getFirstErrors();
        }

        return $this->viewRenderer->render('__form', $parameters);
    }

    public function edit(
        Request $request,
        PostRepository $postRepository,
        ValidatorInterface $validator,
        CurrentRoute $currentRoute
    ): Response {
        $slug = $currentRoute->getArgument('slug');
        $post = $postRepository->fullPostPage($slug);
        if ($post === null) {
            return $this->webService->getNotFoundResponse();
        }

        $parameters = [
            'title' => 'Edit post',
            'action' => ['blog/edit', ['slug' => $slug]],
            'errors' => [],
            'body' => [
                'title' => $post->getTitle(),
                'content' => $post->getContent(),
                'tags' => $this->postService->getPostTags($post),
            ],
        ];

        if ($request->getMethod() === Method::POST) {
            $form = new PostForm();
            $body = $request->getParsedBody();
            if ($form->load($body) && $validator->validate($form)->isValid()) {
                $this->postService->savePost($this->authService->getUser(), $post, $form);
                return $this->webService->getRedirectResponse('blog/index');
            }

            $parameters['body'] = $body;
            $parameters['errors'] = $form->getFormErrors()->getFirstErrors();
        }

        return $this->viewRenderer->render('__form', $parameters);
    }
}
