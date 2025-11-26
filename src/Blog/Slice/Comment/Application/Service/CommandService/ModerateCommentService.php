<?php

declare(strict_types=1);

namespace App\Blog\Slice\Comment\Application\Service\CommandService;

use App\Blog\Domain\Exception\BlogNotFoundException;
use App\Blog\Domain\Port\CommentRepositoryInterface;
use App\Blog\Slice\Comment\Application\Service\QueryService\ModerateCommentQueryServiceInterface;
use Override;

final readonly class ModerateCommentService implements ModerateCommentServiceInterface
{
    public function __construct(
        private CommentRepositoryInterface $repository,
        private ModerateCommentQueryServiceInterface $commentQueryService,
    ) {}

    /**
     * @throws BlogNotFoundException
     */
    #[Override]
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
    #[Override]
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
    #[Override]
    public function moderate(int $commentId, string $commentText, bool $public): void
    {
        if (($comment = $this->commentQueryService->getComment($commentId)) === null) {
            throw new BlogNotFoundException('Comment does not exist!');
        }

        $comment->change($commentText);

        if ($public) {
            $comment->isPublic() ?: $comment->publish();
        } else {
            $comment->toDraft();
        }

        $this->repository->save([$comment]);
    }

    /**
     * @throws BlogNotFoundException
     */
    #[Override]
    public function delete(int $commentId): void
    {
        if (($comment = $this->commentQueryService->getComment($commentId)) === null) {
            throw new BlogNotFoundException('Comment does not exist!');
        }

        $this->repository->delete([$comment]);
    }
}
