<?php

declare(strict_types=1);

namespace App\Blog\Slice\Comment\Application\Service\CommandService;

use App\Blog\Domain\Exception\BlogNotFoundException;
use App\Blog\Domain\Port\CommentRepositoryInterface;
use App\Blog\Domain\User\Commentator;
use App\Blog\Slice\Comment\Application\Service\QueryService\CommentQueryServiceInterface;
use App\Blog\Slice\Post\Service\QueryService\ReadPostQueryServiceInterface;
use Override;

final readonly class CommentService implements CommentServiceInterface
{
    public function __construct(
        private CommentRepositoryInterface $repository,
        private ReadPostQueryServiceInterface $postQueryService,
        private CommentQueryServiceInterface $commentQueryService,
    ) {}

    /**
     * @TODO Frontend
     * @throws BlogNotFoundException
     */
    #[Override]
    public function add(
        int $postId,
        string $commentText,
        Commentator $commentator,
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
    #[Override]
    public function edit(int $commentId, string $commentText): void
    {
        if (($comment = $this->commentQueryService->getComment($commentId)) === null) {
            throw new BlogNotFoundException('Comment does not exist!');
        }

        $comment->change($commentText);

        $this->repository->save([$comment]);
    }

}
