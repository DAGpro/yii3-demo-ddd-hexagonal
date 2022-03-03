<?php

declare(strict_types=1);

namespace App\Blog\Application\Service\AppService\CommandService;

use App\Blog\Application\Service\CommandService\CommentServiceInterface;
use App\Blog\Application\Service\QueryService\CommentQueryServiceInterface;
use App\Blog\Application\Service\QueryService\ReadPostQueryServiceInterface;
use App\Blog\Domain\Exception\BlogNotFoundException;
use App\Blog\Domain\Port\CommentRepositoryInterface;
use App\Blog\Domain\User\Commentator;

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
     * @TODO Frontend
     * @throws BlogNotFoundException
     */
    public function add(
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
        if (($comment = $this->commentQueryService->getComment($commentId)) === null) {
            throw new BlogNotFoundException('Comment does not exist!');
        }

        $comment->change($commentText);

        $this->repository->save([$comment]);
    }

}
