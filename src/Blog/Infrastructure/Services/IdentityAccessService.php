<?php

declare(strict_types=1);

namespace App\Blog\Infrastructure\Services;

use App\Blog\Domain\Comment;
use App\Blog\Domain\Post;
use App\Blog\Domain\User\Author;
use App\Blog\Domain\User\Commentator;
use App\IdentityAccess\User\Application\Service\UserQueryServiceInterface;
use App\Infrastructure\Authentication\AuthenticationService;
use App\Infrastructure\Authorization\AuthorizationService;

final readonly class IdentityAccessService
{
    public function __construct(
        private AuthorizationService $authorizationService,
        private AuthenticationService $authenticationService,
        private UserQueryServiceInterface $userQueryService,
    ) {
    }

    public function findAuthor(string $authorName): ?Author
    {
        $user = $this->userQueryService->findByLogin($authorName);
        if ($user === null || !$this->authorizationService->userHasRole($user->getId(), 'author')) {
            return null;
        }

        return new Author($user->getId(), $user->getLogin());
    }

    public function getAuthor(): ?Author
    {
        $user = $this->authenticationService->getUser();

        if ($user === null || !$this->authorizationService->userHasRole($user->getId(), 'author')) {
            return null;
        }

        return new Author($user->getId(), $user->getLogin());
    }

    public function getCommentator(): ?Commentator
    {
        $user = $this->authenticationService->getUser();
        if ($user === null) {
            return null;
        }

        return new Commentator($user->getId(), $user->getLogin());
    }

    public function isCommentator(Comment $comment): bool
    {
        $commentator = $this->getCommentator();
        return $commentator !== null && $comment->isCommentator($commentator);
    }

    public function isAuthor(Post $post): bool
    {
        $author = $this->getAuthor();
        return $author !== null && $post->isAuthor($author);
    }

}
