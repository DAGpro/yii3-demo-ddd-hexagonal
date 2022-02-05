<?php

declare(strict_types=1);

namespace App\Core\Component\Blog\Application\Service\AppService\CommandService;

use App\Core\Component\Blog\Application\Service\CommandService\CommentServiceInterface;
use App\Core\Component\Blog\Application\Service\QueryService\CommentQueryServiceInterface;
use App\Core\Component\Blog\Application\Service\QueryService\ReadPostQueryServiceInterface;
use App\Core\Component\Blog\Domain\Exception\BlogNotFoundException;
use App\Core\Component\Blog\Domain\Port\CommentRepositoryInterface;
use App\Core\Component\Blog\Domain\User\Commentator;

final class CommentService implements CommentServiceInterface
{
    private CommentRepositoryInterface $repository;
    private ReadPostQueryServiceInterface $postQueryService;
    private CommentQueryServiceInterface $commentQueryService;

    public function __construct(
        CommentRepositoryInterface $repository,
        ReadPostQueryServiceInterface $postQueryService,
        CommentQueryServiceInterface $commentQueryService
    ) {
        $this->repository = $repository;
        $this->postQueryService = $postQueryService;
        $this->commentQueryService = $commentQueryService;
    }

    /**
     * @throws BlogNotFoundException
     */
    public function draft(int $commentId): void
    {
        if (($comment = $this->commentQueryService->getComment($commentId)) === null) {
            throw new BlogNotFoundException('Comment does not exist!');
        }

        !$comment->isPublic() ?: $comment->setPublic(false);

        $this->repository->save([$comment]);
    }

    /**
     * @throws BlogNotFoundException
     */
    public function public(int $commentId): void
    {
        if (($comment = $this->commentQueryService->getComment($commentId)) === null) {
            throw new BlogNotFoundException('Comment does not exist!');
        }

        $comment->isPublic() ?: $comment->setPublic(true);

        $this->repository->save([$comment]);
    }

    /**
     * @throws BlogNotFoundException
     */
    public function moderate(int $commentId, string $commentText, bool $public): void
    {
        if (($comment = $this->commentQueryService->getComment($commentId)) === null) {
            throw new BlogNotFoundException('Comment does not exist!');
        }

        $comment->setContent($commentText);
        $comment->setPublic($public);

        $this->repository->save([$comment]);
    }

    /**
     * @TODO Frontend
     * @throws BlogNotFoundException
     */
    public function addComment(
        int $postId,
        string $commentText,
        Commentator $commentator
    ): void {
        if (($post = $this->postQueryService->getPost($postId)) === null) {
            throw new BlogNotFoundException('Post does not exist!');
        }
        $comment = $post->createComment($commentText, $commentator);

        $this->repository->save([$comment]);
    }

    /**
     * @TODO Frontend
     * @throws BlogNotFoundException
     */
    public function edit(int $commentId, string $commentText): void
    {
        if (($comment = $this->commentQueryService->getPublicComment($commentId)) === null) {
            throw new BlogNotFoundException('Comment does not exist!');
        }

        $comment->setContent($commentText);

        $this->repository->save([$comment]);
    }

    /**
     * @throws BlogNotFoundException
     */
    public function delete(int $commentId): void
    {
        if (($comment = $this->commentQueryService->getComment($commentId)) === null) {
            throw new BlogNotFoundException('Comment does not exist!');
        }

        $this->repository->delete([$comment]);
    }
}
