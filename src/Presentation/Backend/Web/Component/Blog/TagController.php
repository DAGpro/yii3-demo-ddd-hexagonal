<?php

declare(strict_types=1);

namespace App\Presentation\Backend\Web\Component\Blog;

use App\Blog\Application\Service\CommandService\TagServiceInterface;
use App\Blog\Application\Service\QueryService\TagQueryServiceInterface;
use App\Blog\Domain\Exception\BlogNotFoundException;
use App\Presentation\Backend\Web\Component\Blog\Form\TagForm;
use App\Presentation\Infrastructure\Web\Service\WebControllerService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Yiisoft\Data\Paginator\OffsetPaginator;
use Yiisoft\Http\Method;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Validator\ValidatorInterface;
use Yiisoft\Yii\View\ViewRenderer;

final class TagController
{
    private const POSTS_PER_PAGE = 3;

    private TagQueryServiceInterface $tagQueryService;
    private TagServiceInterface $tagService;
    private ViewRenderer $view;
    private WebControllerService $webService;

    public function __construct(
        TagQueryServiceInterface $tagQueryService,
        TagServiceInterface $tagService,
        ViewRenderer $viewRenderer,
        WebControllerService $webService
    ) {
        $this->tagQueryService = $tagQueryService;
        $this->tagService = $tagService;
        $this->webService = $webService;
        $viewRenderer = $viewRenderer->withLayout('@backendLayout/main');
        $viewRenderer = $viewRenderer->withViewPath('@backendView/component/blog');
        $this->view = $viewRenderer->withControllerName('tag');
    }

    public function index(CurrentRoute $currentRoute): ResponseInterface
    {
        $pageNum = (int)$currentRoute->getArgument('page', '1');

        $dataReader = $this->tagQueryService->findAllPreloaded();

        $paginator = (new OffsetPaginator($dataReader))
            ->withPageSize(self::POSTS_PER_PAGE)
            ->withCurrentPage($pageNum);

        $data = [
            'paginator' => $paginator,
        ];

        return $this->view->render('index', $data);
    }

    public function changeTag(
        Request $request,
        CurrentRoute $currentRoute,
        ValidatorInterface $validator
    ): ResponseInterface {
        $tagId = (int)$currentRoute->getArgument('tag_id');

        if (($tag = $this->tagQueryService->getTag($tagId)) === null) {
            return $this->webService->notFound();
        }

        $form = new TagForm($tag);
        if (($request->getMethod() === Method::POST)
            && $form->load($request->getParsedBody())
            && $validator->validate($form)->isValid()
        ) {
            $this->tagService->changeTag($tag->getId(), $form->getLabel());

            return $this->webService->redirect('backend/tag');
        }

        return $this->view->render(
            'change-tag',
            [
                'title' => 'Change tag label',
                'action' => ['backend/tag/change', ['tag_id' => $tagId]],
                'form' => $form
            ]
        );
    }

    public function delete(Request $request): ResponseInterface
    {
        $body = $request->getParsedBody();
        $tagId = !empty($body['tag_id']) ? $body['tag_id'] : null;

        if ($tagId === null) {
            return $this->webService->sessionFlashAndRedirect(
                'The tag_id parameter is required in the post request!',
                'backend/tag',
                [],
                'danger',
            );
        }

        try {
            $this->tagService->delete((int)$tagId);
        } catch (BlogNotFoundException $exception) {
            return $this->webService->notFound();
        }

        return $this->webService->sessionFlashAndRedirect(
            'Tag successfully deleted!',
            'backend/tag'
        );
    }
}
