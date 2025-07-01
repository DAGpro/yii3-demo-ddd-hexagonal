<?php

declare(strict_types=1);

namespace App\Blog\Presentation\Frontend\Web\Comment;

use App\Blog\Application\Service\AppService\QueryService\ReadPostQueryService;
use App\Blog\Application\Service\CommandService\CommentServiceInterface;
use App\Blog\Application\Service\QueryService\CommentQueryServiceInterface;
use App\Blog\Domain\Exception\BlogNotFoundException;
use App\Blog\Infrastructure\Services\IdentityAccessService;
use App\Infrastructure\Presentation\Web\Service\WebControllerService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Yiisoft\Data\Paginator\PageToken;
use Yiisoft\FormModel\FormHydrator;
use Yiisoft\Http\Method;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Yii\View\Renderer\ViewRenderer;

final readonly class CommentController
{
    private ViewRenderer $view;

    public function __construct(
        ViewRenderer $viewRenderer,
        private WebControllerService $webService,
        private ReadPostQueryService $postQueryService,
        private IdentityAccessService $identityAccessService,
    ) {
        $this->view = $viewRenderer->withViewPath('@blogView/comment');
    }

    public function index(
        Request $request,
        CurrentRoute $currentRoute,
        CommentQueryServiceInterface $commentQueryService,
    ): Response {
        $paginator = $commentQueryService->getFeedPaginator();
        if ($currentRoute->getArgument('next') !== null) {
            $paginator = $paginator->withToken(PageToken::next((string)$currentRoute->getArgument('next')));
        }

        if ($this->isAjaxRequest($request)) {
            return $this->view->renderPartial('_comments', ['data' => $paginator]);
        }

        return $this->view->render('index', ['data' => $paginator]);
    }

    public function add(
        Request $request,
        CurrentRoute $currentRoute,
        CommentServiceInterface $commentService,
        CommentForm $form,
        FormHydrator $formHydrator,
    ): Response {
        $postSlug = $currentRoute->getArgument('slug', '');

        if (($commentator = $this->identityAccessService->getCommentator()) === null) {
            return $this->webService->accessDenied();
        }

        if (($post = $this->postQueryService->getPostBySlug($postSlug)) === null) {
            return $this->webService->sessionFlashAndRedirect(
                'This post does not exist!',
                'blog',
                [],
                'danger',
            );
        }

        if ($request->getMethod() === Method::POST
            && $formHydrator->populateFromPostAndValidate($form, $request)
        ) {
            try {
                $commentService->add(
                    $post->getId(),
                    $form->getComment(),
                    $commentator,
                );
            } catch (BlogNotFoundException $exception) {
                return $this->webService->sessionFlashAndRedirect(
                    $exception->getMessage(),
                    'blog',
                    [],
                    'danger',
                );
            }
        }
        return $this->webService->sessionFlashAndRedirect(
            'Message sent successfully, will be published after moderation',
            'blog/post',
            ['slug' => $postSlug],
        );
    }

    public function edit(
        Request $request,
        CurrentRoute $currentRoute,
        CommentQueryServiceInterface $commentQueryService,
        FormHydrator $formHydrator,
        CommentServiceInterface $commentService,
    ): Response {
        $commentId = (int)$currentRoute->getArgument('comment_id');

        $comment = $commentQueryService->getComment($commentId);
        if ($comment === null) {
            return $this->webService->notFound();
        }

        if (!$this->identityAccessService->isCommentator($comment)) {
            return $this->webService->accessDenied();
        }

        $form = new CommentForm($comment);
        if ($request->getMethod() === Method::POST
            && $formHydrator->populateFromPostAndValidate($form, $request)
        ) {
            try {
                $commentService->edit($comment->getId(), $form->getComment());
            } catch (BlogNotFoundException $exception) {
                return $this->webService->sessionFlashAndRedirect(
                    $exception->getMessage(),
                    'blog/comment/index',
                    [],
                    'danger',
                );
            }

            return $this->webService->redirect('blog/comment/index');
        }

        return $this->view->render('comment_form', [
            'action' => ['blog/comment/edit', ['comment_id' => $commentId]],
            'form' => $form,
        ]);
    }

    private function isAjaxRequest(Request $request): bool
    {
        return $request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest';
    }
}
