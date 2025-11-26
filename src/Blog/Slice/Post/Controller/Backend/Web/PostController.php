<?php

declare(strict_types=1);

namespace App\Blog\Slice\Post\Controller\Backend\Web;

use App\Blog\Domain\Exception\BlogNotFoundException;
use App\Blog\Slice\Post\Controller\Backend\Web\Form\PostForm;
use App\Blog\Slice\Post\Service\CommandService\ModeratePostServiceInterface;
use App\Blog\Slice\Post\Service\CommandService\PostModerateDTO;
use App\Blog\Slice\Post\Service\QueryService\ModeratePostQueryServiceInterface;
use App\Infrastructure\Presentation\Web\Service\WebControllerService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Yiisoft\Data\Paginator\OffsetPaginator;
use Yiisoft\FormModel\FormHydrator;
use Yiisoft\Http\Method;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Yii\View\Renderer\ViewRenderer;

final readonly class PostController
{
    private const int POSTS_PER_PAGE = 10;
    private ViewRenderer $view;

    public function __construct(
        private ModeratePostQueryServiceInterface $postQueryService,
        private ModeratePostServiceInterface $postService,
        ViewRenderer $viewRenderer,
        private WebControllerService $webService,
    ) {
        $this->view = $viewRenderer
            ->withLayout('@backendLayout/main')
            ->withViewPath(__DIR__ . '/view');
    }

    public function index(CurrentRoute $currentRoute): ResponseInterface
    {
        $pageNum = max(1, (int) $currentRoute->getArgument('page', '1'));

        $dataReader = $this->postQueryService->findAllPreloaded();

        $paginator = new OffsetPaginator($dataReader)
            ->withPageSize(self::POSTS_PER_PAGE)
            ->withCurrentPage($pageNum);

        return $this->view->render(
            'index',
            [
                'paginator' => $paginator,
            ],
        );
    }

    public function view(CurrentRoute $currentRoute): ResponseInterface
    {
        $postId = (int) $currentRoute->getArgument('post_id');
        if (($post = $this->postQueryService->getPost($postId)) === null) {
            return $this->webService->notFound();
        }

        return $this->view->render(
            'view',
            [
                'post' => $post,
            ],
        );
    }

    public function draftPost(Request $request): ResponseInterface
    {
        $body = $request->getParsedBody();
        $postId = !empty($body['post_id']) ? $body['post_id'] : null;

        if ($postId === null) {
            return $this->webService->sessionFlashAndRedirect(
                'The post_id parameter is required in the post request!',
                'backend/post',
                [],
                'danger',
            );
        }

        try {
            $this->postService->draft((int) $postId);
        } catch (BlogNotFoundException) {
            return $this->webService->notFound();
        }

        return $this->webService->sessionFlashAndRedirect(
            'Post moved to draft!',
            'backend/post/view',
            ['post_id' => (int) $postId],
        );
    }

    public function publicPost(Request $request): ResponseInterface
    {
        $body = $request->getParsedBody();
        $postId = !empty($body['post_id']) ? $body['post_id'] : null;

        if ($postId === null) {
            return $this->webService->sessionFlashAndRedirect(
                'The post_id parameter is required in the post request!',
                'backend/post',
                [],
                'danger',
            );
        }

        try {
            $this->postService->public((int) $postId);
        } catch (BlogNotFoundException) {
            return $this->webService->notFound();
        }

        return $this->webService->sessionFlashAndRedirect(
            'Post published!',
            'backend/post/view',
            ['post_id' => (int) $postId],
        );
    }

    public function moderate(
        Request $request,
        CurrentRoute $currentRoute,
        FormHydrator $formHydrator,
    ): ResponseInterface {
        $postId = (int) $currentRoute->getArgument('post_id');
        if (($post = $this->postQueryService->getPost($postId)) === null || ($postId = $post->getId()) === null) {
            return $this->webService->notFound();
        }

        $form = new PostForm($post);
        if (($request->getMethod() === Method::POST)
            && $formHydrator->populateFromPostAndValidate($form, $request)
        ) {
            try {
                $this->postService->moderate(
                    $postId,
                    new PostModerateDTO(
                        $form->getTitle(),
                        $form->getContent(),
                        $form->getPublic(),
                        $form->getTags(),
                    ),
                );
            } catch (BlogNotFoundException) {
                return $this->webService->notFound();
            }

            return $this->webService->redirect('backend/post/view', ['post_id' => $postId]);
        }

        return $this->view->render('moderate', [
            'form' => $form,
        ]);
    }

    public function delete(Request $request): ResponseInterface
    {
        $body = $request->getParsedBody();
        $postId = !empty($body['post_id']) ? $body['post_id'] : null;

        if ($postId === null) {
            return $this->webService->sessionFlashAndRedirect(
                'The post_id parameter is required in the post request!',
                'backend/post',
                [],
                'danger',
            );
        }

        try {
            $this->postService->delete((int) $postId);
        } catch (BlogNotFoundException) {
            return $this->webService->notFound();
        }

        return $this->webService->sessionFlashAndRedirect(
            'Post successfully deleted!',
            'backend/post',
        );
    }
}
