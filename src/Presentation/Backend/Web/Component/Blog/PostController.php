<?php

declare(strict_types=1);

namespace App\Presentation\Backend\Web\Component\Blog;

use App\Core\Component\Blog\Application\Service\CommandService\ModeratePostServiceInterface;
use App\Core\Component\Blog\Application\Service\CommandService\PostModerateDTO;
use App\Core\Component\Blog\Application\Service\QueryService\ModeratePostQueryServiceInterface;
use App\Core\Component\Blog\Domain\Exception\BlogNotFoundException;
use App\Presentation\Backend\Web\Component\Blog\Form\PostForm;
use App\Presentation\Infrastructure\Web\Service\WebControllerService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Yiisoft\Data\Paginator\OffsetPaginator;
use Yiisoft\Http\Method;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Validator\ValidatorInterface;
use Yiisoft\Yii\View\ViewRenderer;

final class PostController
{
    private const POSTS_PER_PAGE = 3;

    private ModeratePostQueryServiceInterface $postQueryService;
    private ModeratePostServiceInterface $postService;
    private ViewRenderer $view;
    private WebControllerService $webService;

    public function __construct(
        ModeratePostQueryServiceInterface $postQueryService,
        ModeratePostServiceInterface $postService,
        ViewRenderer $viewRenderer,
        WebControllerService $webService
    ) {
        $this->postQueryService = $postQueryService;
        $this->postService = $postService;
        $this->webService = $webService;
        $viewRenderer = $viewRenderer->withLayout('@backendLayout/main');
        $viewRenderer = $viewRenderer->withViewPath('@backendView/component/blog');
        $this->view = $viewRenderer->withControllerName('post');
    }

    public function index(CurrentRoute $currentRoute): ResponseInterface
    {
        $pageNum = (int)$currentRoute->getArgument('page', '1');

        $dataReader = $this->postQueryService->findAllPreloaded();

        $paginator = (new OffsetPaginator($dataReader))
            ->withPageSize(self::POSTS_PER_PAGE)
            ->withCurrentPage($pageNum);

        return $this->view->render(
            'index',
            [
                'paginator' => $paginator,
            ]
        );
    }

    public function view(CurrentRoute $currentRoute): ResponseInterface
    {
        $postId = (int)$currentRoute->getArgument('post_id');
        if (($post = $this->postQueryService->getPost($postId)) === null) {
            return $this->webService->notFound();
        }

        return $this->view->render(
            'view',
            [
                'post' => $post,
            ]
        );
    }

    public function draftPost(Request $request): ResponseInterface
    {
        $body = $request->getParsedBody();
        $postId = $body['post_id'] !== '' ? $body['post_id'] : null;

        if ($postId === null) {
            return $this->webService->sessionFlashAndRedirect(
                'The post_id parameter is required in the post request!',
                'backend/post',
                [],
                'danger'
            );
        }

        try {
            $this->postService->draft((int)$postId);
        } catch (BlogNotFoundException $exception) {
            return $this->webService->notFound();
        }

        return $this->webService->sessionFlashAndRedirect(
            'Post moved to draft!',
            'backend/post/view',
            ['post_id' => $postId]
        );
    }

    public function publicPost(Request $request): ResponseInterface
    {
        $body = $request->getParsedBody();
        $postId = $body['post_id'] !== '' ? $body['post_id'] : null;

        if ($postId === null) {
            return $this->webService->sessionFlashAndRedirect(
                'The post_id parameter is required in the post request!',
                'backend/post',
                [],
                'danger'
            );
        }

        try {
            $this->postService->public((int)$postId);
        } catch (BlogNotFoundException $exception) {
            return $this->webService->notFound();
        }

        return $this->webService->sessionFlashAndRedirect(
            'Post published!',
            'backend/post/view',
            ['post_id' => $postId]
        );
    }

    public function moderate(
        Request $request,
        CurrentRoute $currentRoute,
        ValidatorInterface $validator
    ): ResponseInterface {
        $postId = (int)$currentRoute->getArgument('post_id');
        if (($post = $this->postQueryService->getPost($postId)) === null) {
            return $this->webService->notFound();
        }

        $form = new PostForm($post);
        if (($request->getMethod() === Method::POST)
            && $form->load($request->getParsedBody())
            && $validator->validate($form)->isValid()
        ) {
            try {
                $this->postService->moderate(
                    $post->getId(),
                    new PostModerateDTO(
                        $form->getTitle(),
                        $form->getContent(),
                        $form->getPublic(),
                        $form->getTags()
                    )
                );
            } catch (BlogNotFoundException $exception) {
                return $this->webService->notFound();
            }

            return $this->webService->redirect('backend/post/view', ['post_id' => $postId]);
        }

        return $this->view->render('moderate', [
            'action' => ['backend/post/moderate', ['post_id' => $post->getId()]],
            'form' => $form
        ]);
    }

    public function delete(Request $request): ResponseInterface
    {
        $body = $request->getParsedBody();
        $postId = $body['post_id'] !== '' ? $body['post_id'] : null;

        if ($postId === null) {
            return $this->webService->sessionFlashAndRedirect(
                'The post_id parameter is required in the post request!',
                'backend/post',
                [],
                'danger'
            );
        }

        try {
            $this->postService->delete((int)$postId);
        } catch (BlogNotFoundException $exception) {
            return $this->webService->notFound();
        }

        return $this->webService->sessionFlashAndRedirect(
            'Post successfully deleted!',
            'backend/post'
        );
    }
}
