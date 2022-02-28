<?php

namespace App\Core\Component\Blog\Application\Service\AppService\CommandService;

use App\Core\Component\Blog\Application\Service\CommandService\ModerateCommentServiceInterface;
use App\Core\Component\Blog\Application\Service\QueryService\ModerateCommentQueryServiceInterface;
use App\Core\Component\Blog\Domain\Exception\BlogNotFoundException;
use App\Core\Component\Blog\Domain\Port\CommentRepositoryInterface;

class ModerateCommentService implements ModerateCommentServiceInterface
{

    private CommentRepositoryInterface $repository;
    private ModerateCommentQueryServiceInterface $commentQueryService;

    public function __construct(
        CommentRepositoryInterface $repository,
        ModerateCommentQueryServiceInterface $commentQueryService
    ) {
        $this->repository = $repository;
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

        $comment->toDraft();

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

        $comment->isPublic() ?: $comment->publish();

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

        $comment->change($commentText);

        if ($public) {
            !$comment->isPublic() ?: $comment->publish();
        } else {
            $comment->toDraft();
        }

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
