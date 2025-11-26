<?php

declare(strict_types=1);

namespace App\Blog\Slice\Comment\Presentation\Backend\Web;

use App\Blog\Domain\Exception\BlogNotFoundException;
use App\Blog\Slice\Comment\Application\Service\CommandService\ModerateCommentServiceInterface;
use App\Blog\Slice\Comment\Application\Service\QueryService\ModerateCommentQueryServiceInterface;
use App\Infrastructure\Presentation\Web\Service\WebControllerService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Yiisoft\Data\Paginator\OffsetPaginator;
use Yiisoft\Data\Reader\OrderHelper;
use Yiisoft\FormModel\FormHydrator;
use Yiisoft\Http\Method;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Yii\View\Renderer\ViewRenderer;

final readonly class CommentController
{
    private const int COMMENT_PER_PAGE = 10;

    private ViewRenderer $view;

    public function __construct(
        private ModerateCommentQueryServiceInterface $commentQueryService,
        private ModerateCommentServiceInterface $commentService,
        ViewRenderer $viewRenderer,
        private WebControllerService $webService,
    ) {
        $this->view = $viewRenderer
            ->withLayout('@backendLayout/main')
            ->withViewPath(__DIR__ . '/view');
    }

    public function index(Request $request, CurrentRoute $currentRoute): ResponseInterface
    {
        $pageNum = max(1, (int) $currentRoute->getArgument('page', '1'));
        $queryParams = $request->getQueryParams();
        $sort = (string) ($queryParams['sort'] ?? 'id');
        $order = OrderHelper::stringToArray($sort);

        $dataReader = $this->commentQueryService->findAllPreloaded();
        $dataReader = $dataReader->withSort(
            $dataReader
                ->getSort()
                ?->withOrder($order),
        );

        $paginator = new OffsetPaginator($dataReader)
            ->withPageSize(self::COMMENT_PER_PAGE)
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
        $commentId = (int) $currentRoute->getArgument('comment_id');
        if (($comment = $this->commentQueryService->getComment($commentId)) === null) {
            return $this->webService->notFound();
        }

        return $this->view->render(
            'view',
            [
                'comment' => $comment,
            ],
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
                'danger',
            );
        }

        try {
            $this->commentService->draft((int) $commentId);
        } catch (BlogNotFoundException) {
            return $this->webService->notFound();
        }

        return $this->webService->sessionFlashAndRedirect(
            'Comment moved to draft!',
            'backend/comment/view',
            ['comment_id' => (int) $commentId],
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
                'danger',
            );
        }

        try {
            $this->commentService->public((int) $commentId);
        } catch (BlogNotFoundException) {
            return $this->webService->notFound();
        }

        return $this->webService->sessionFlashAndRedirect(
            'Comment published!',
            'backend/comment/view',
            ['comment_id' => (int) $commentId],
        );
    }

    public function moderateComment(
        Request $request,
        CurrentRoute $currentRoute,
        FormHydrator $formHydrator,
    ): ResponseInterface {
        $commentId = (int) $currentRoute->getArgument('comment_id');
        if (($comment = $this->commentQueryService->getComment($commentId)) === null) {
            return $this->webService->notFound();
        }

        $form = new CommentForm($comment);
        if (($request->getMethod() === Method::POST)
            && $formHydrator->populateFromPostAndValidate($form, $request)
        ) {
            try {
                $this->commentService->moderate($form->getCommentId(), $form->getContent(), $form->getPublic());
            } catch (BlogNotFoundException) {
                return $this->webService->notFound();
            }

            return $this->webService->redirect('backend/comment/view', [
                'comment_id' => $commentId,
            ]);
        }

        return $this->view->render(
            'moderate',
            [
                'form' => $form,
            ],
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
                'danger',
            );
        }

        try {
            $this->commentService->delete((int) $commentId);
        } catch (BlogNotFoundException) {
            return $this->webService->notFound();
        }

        return $this->webService->sessionFlashAndRedirect(
            'Comment successfully deleted!',
            'backend/comment',
        );
    }
}
