<?php

declare(strict_types=1);

namespace App\Blog\Presentation\Frontend\Web\Post;

use App\Blog\Application\Service\QueryService\ReadPostQueryServiceInterface;
use App\Blog\Infrastructure\Services\IdentityAccessService;
use App\Infrastructure\Presentation\Web\Service\WebControllerService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Yii\View\Renderer\ViewRenderer;

final readonly class PostController
{
    private ViewRenderer $view;

    public function __construct(
        ViewRenderer $viewRenderer,
        private WebControllerService $webService,
        private IdentityAccessService $identityAccessService,
    ) {
        $this->view = $viewRenderer->withViewPath('@blogView/post');
    }

    public function index(
        CurrentRoute $currentRoute,
        ReadPostQueryServiceInterface $postQueryService,
    ): Response {
        $slug = $currentRoute->getArgument('slug');
        if ($slug === null) {
            return $this->webService->notFound();
        }

        $item = $postQueryService->fullPostPage($slug);
        if ($item === null) {
            return $this->webService->notFound();
        }

        $canEdit = $this->identityAccessService->isAuthor($item);
        $commentator = $this->identityAccessService->getCommentator();

        return $this->view->render('index', [
            'item' => $item,
            'canEdit' => $canEdit,
            'commentator' => $commentator,
            'slug' => $slug,
        ]);
    }

    public function findAuthorPosts(Request $request, ReadPostQueryServiceInterface $postQueryService): Response
    {
        $authorName = $request->getAttribute('author');
        if ($authorName === null) {
            return $this->webService->notFound();
        }

        $author = $this->identityAccessService->findAuthor((string)$authorName);
        if ($author === null) {
            return $this->webService->notFound();
        }

        $data = $postQueryService->findByAuthor($author);

        $currentAuthor = $this->identityAccessService->getAuthor();
        $canEdit = $currentAuthor !== null && $author->isEqual($currentAuthor);

        return $this->view->render('author-posts', ['dataReader' => $data, 'canEdit' => $canEdit]);
    }

}
