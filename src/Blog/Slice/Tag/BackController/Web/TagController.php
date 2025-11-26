<?php

declare(strict_types=1);

namespace App\Blog\Slice\Tag\BackController\Web;

use App\Blog\Domain\Exception\BlogNotFoundException;
use App\Blog\Slice\Tag\Service\CommandService\TagServiceInterface;
use App\Blog\Slice\Tag\Service\QueryService\TagQueryServiceInterface;
use App\Infrastructure\Presentation\Web\Service\WebControllerService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Yiisoft\Data\Paginator\OffsetPaginator;
use Yiisoft\FormModel\FormHydrator;
use Yiisoft\Http\Method;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Yii\View\Renderer\ViewRenderer;

final readonly class TagController
{
    private const int POSTS_PER_PAGE = 10;
    private ViewRenderer $view;

    public function __construct(
        private TagQueryServiceInterface $tagQueryService,
        private TagServiceInterface $tagService,
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

        $dataReader = $this->tagQueryService->findAllPreloaded();

        $paginator = new OffsetPaginator($dataReader)
            ->withPageSize(self::POSTS_PER_PAGE)
            ->withCurrentPage($pageNum);

        return $this->view->render(
            'index',
            ['paginator' => $paginator],
        );
    }

    public function changeTag(
        Request $request,
        CurrentRoute $currentRoute,
        FormHydrator $formHydrator,
    ): ResponseInterface {
        $tagId = (int) $currentRoute->getArgument('tag_id');

        if (($tag = $this->tagQueryService->getTag($tagId)) === null || ($tagId = $tag->getId()) === null) {
            return $this->webService->notFound();
        }

        $form = new TagForm($tag);
        if (($request->getMethod() === Method::POST)
            && $formHydrator->populateFromPostAndValidate($form, $request)
        ) {
            $this->tagService->changeTag($tagId, $form->getLabel());

            return $this->webService->redirect('backend/tag');
        }

        return $this->view->render(
            'change-tag',
            [
                'title' => 'Change tag label',
                'form' => $form,
            ],
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
            $this->tagService->delete((int) $tagId);
        } catch (BlogNotFoundException) {
            return $this->webService->notFound();
        }

        return $this->webService->sessionFlashAndRedirect(
            'Tag successfully deleted!',
            'backend/tag',
        );
    }
}
