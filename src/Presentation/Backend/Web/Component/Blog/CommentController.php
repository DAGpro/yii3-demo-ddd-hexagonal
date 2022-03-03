<?php

declare(strict_types=1);

namespace App\Presentation\Backend\Web\Component\Blog;

use App\Blog\Application\Service\CommandService\ModerateCommentServiceInterface;
use App\Blog\Application\Service\QueryService\ModerateCommentQueryServiceInterface;
use App\Blog\Domain\Exception\BlogNotFoundException;
use App\Presentation\Backend\Web\Component\Blog\Form\CommentForm;
use App\Presentation\Infrastructure\Web\Service\WebControllerService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Yiisoft\Data\Paginator\OffsetPaginator;
use Yiisoft\Http\Method;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Validator\ValidatorInterface;
use Yiisoft\Yii\View\ViewRenderer;

final class CommentController
{
    private const COMMENT_PER_PAGE = 3;

    private ModerateCommentQueryServiceInterface $commentQueryService;
    private ModerateCommentServiceInterface $commentService;
    private ViewRenderer $view;
    private WebControllerService $webService;

    public function __construct(
        ModerateCommentQueryServiceInterface $commentQueryService,
        ModerateCommentServiceInterface $commentService,
        ViewRenderer $viewRenderer,
        WebControllerService $webService
    ) {
        $this->commentQueryService = $commentQueryService;
        $this->commentService = $commentService;
        $this->webService = $webService;
        $viewRenderer = $viewRenderer->withLayout('@backendLayout/main');
        $viewRenderer = $viewRenderer->withViewPath('@backendView/component/blog');
        $this->view = $viewRenderer->withControllerName('comment');
    }

    public function index(CurrentRoute $currentRoute): ResponseInterface
    {
        $pageNum = (int)$currentRoute->getArgument('page', '1');

        $dataReader = $this->commentQueryService->findAllPreloaded();

        $paginator = (new OffsetPaginator($dataReader))
            ->withPageSize(self::COMMENT_PER_PAGE)
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
        $commentId = (int)$currentRoute->getArgument('comment_id');
        if (($comment = $this->commentQueryService->getComment($commentId)) === null) {
            return $this->webService->notFound();
        }

        return $this->view->render(
            'view',
            [
                'comment' => $comment,
            ]
        );
    }

    public function draftComment(Request $request): ResponseInterface
    {
        $body = $request->getParsedBody();
        $commentId = !empty($body['comment_id']) ? $body['comment_id'] : null;

        if ($commentId === null) {
            return $this->webService->sessionFlashAndRedirect(
                'The comment_id parameter is required in the post request!',
                'backend/comment',
                [],
                'danger'
            );
        }

        try {
            $this->commentService->draft((int)$commentId);
        } catch (BlogNotFoundException $exception) {
            return $this->webService->notFound();
        }

        return $this->webService->sessionFlashAndRedirect(
            'Comment moved to draft!',
            'backend/comment/view',
            ['comment_id' => $commentId]
        );
    }

    public function publicComment(Request $request): ResponseInterface
    {
        $body = $request->getParsedBody();
        $commentId = !empty($body['comment_id']) ? $body['comment_id'] : null;

        if ($commentId === null) {
            return $this->webService->sessionFlashAndRedirect(
                'The comment_id parameter is required in the post request!',
                'backend/comment',
                [],
                'danger'
            );
        }

        try {
            $this->commentService->public((int)$commentId);
        } catch (BlogNotFoundException $exception) {
            return $this->webService->notFound();
        }

        return $this->webService->sessionFlashAndRedirect(
            'Comment published!',
            'backend/comment/view',
            ['comment_id' => $commentId]
        );
    }

    public function moderateComment(
        Request $request,
        CurrentRoute $currentRoute,
        ValidatorInterface $validator
    ): ResponseInterface {
        $commentId = (int)$currentRoute->getArgument('comment_id');
        if (($comment = $this->commentQueryService->getComment($commentId)) === null) {
            return $this->webService->notFound();
        }

        $form = new CommentForm($comment);
        if (($request->getMethod() === Method::POST)
            && $form->load($request->getParsedBody())
            && $validator->validate($form)->isValid()
        ) {
            try {
                $this->commentService->moderate((int)$form->getCommentId(), $form->getContent(), $form->getPublic());
            } catch (BlogNotFoundException $exception) {
                return $this->webService->notFound();
            }

            return $this->webService->redirect('backend/comment/view', [
                'comment_id' => $commentId
            ]);
        }

        return $this->view->render(
            'moderate',
            [
                'action' => ['backend/comment/moderate', ['comment_id' => $comment->getId()]],
                'form' => $form,
            ]
        );
    }

    public function delete(Request $request): ResponseInterface
    {
        $body = $request->getParsedBody();
        $commentId = !empty($body['comment_id']) ? $body['comment_id'] : null;

        if ($commentId === null) {
            return $this->webService->sessionFlashAndRedirect(
                'The comment_id parameter is required in the post request!',
                'backend/comment',
                [],
                'danger'
            );
        }

        try {
            $this->commentService->delete((int)$commentId);
        } catch (BlogNotFoundException $exception) {
            return $this->webService->notFound();
        }

        return $this->webService->sessionFlashAndRedirect(
            'Comment successfully deleted!',
            'backend/comment'
        );
    }
}
