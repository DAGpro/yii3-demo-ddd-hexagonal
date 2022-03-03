<?php

declare(strict_types=1);

namespace App\Presentation\Frontend\Web\Component\Blog\Post;

use App\Blog\Application\Service\QueryService\ReadPostQueryServiceInterface;
use App\Blog\Infrastructure\Services\IdentityAccessService;
use App\Presentation\Infrastructure\Web\Service\WebControllerService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Yii\View\ViewRenderer;

final class PostController
{
    private ViewRenderer $view;
    private WebControllerService $webService;
    private IdentityAccessService $identityAccessService;

    public function __construct(
        ViewRenderer $viewRenderer,
        WebControllerService $webService,
        IdentityAccessService $identityAccessService
    ) {
        $this->view = $viewRenderer->withControllerName('component/blog/post');
        $this->webService = $webService;
        $this->identityAccessService = $identityAccessService;
    }

    public function index(
        CurrentRoute $currentRoute,
        ReadPostQueryServiceInterface $postQueryService
    ): Response {
        $slug = $currentRoute->getArgument('slug', '');

        if (($item = $postQueryService->fullPostPage($slug)) === null) {
            return $this->webService->notFound();
        }

        $canEdit = $this->identityAccessService->isAuthor($item);
        $commentator = $this->identityAccessService->getCommentator();

        return $this->view->render('index', [
            'item' => $item,
            'canEdit' => $canEdit,
            'commentator' => $commentator,
            'slug' => $slug
        ]);
    }

    public function findAuthorPosts(Request $request, ReadPostQueryServiceInterface $postQueryService): Response
    {
        $authorName = $request->getAttribute('author', '');
        $author = $this->identityAccessService->findAuthor($authorName);
        if ($author === null) {
            return $this->webService->notFound();
        }

        $data = $postQueryService->findByAuthor($author);

        $currentAuthor = $this->identityAccessService->getAuthor();
        $canEdit = $currentAuthor !== null && $author->isEqual($currentAuthor);

        return $this->view->render('author-posts', ['dataReader' => $data, 'canEdit' => $canEdit]);
    }

}
