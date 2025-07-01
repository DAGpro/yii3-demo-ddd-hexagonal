<?php

declare(strict_types=1);

namespace App\Blog\Application\Service\AppService\CommandService;

use App\Blog\Application\Service\CommandService\ModeratePostServiceInterface;
use App\Blog\Application\Service\CommandService\PostModerateDTO;
use App\Blog\Application\Service\QueryService\ModeratePostQueryServiceInterface;
use App\Blog\Domain\Exception\BlogNotFoundException;
use App\Blog\Domain\Port\PostRepositoryInterface;
use App\Blog\Domain\Port\TagRepositoryInterface;

final readonly class ModeratePostService implements ModeratePostServiceInterface
{
    public function __construct(
        private ModeratePostQueryServiceInterface $postQueryService,
        private PostRepositoryInterface $repository,
        private TagRepositoryInterface $tagRepository,
    ) {
    }

    /**
     * @throws BlogNotFoundException
     */
    #[\Override]
    public function public(int $postId): void
    {
        if (($post = $this->postQueryService->getPost($postId)) === null) {
            throw new BlogNotFoundException('This post does not exist!');
        }

        $post->publish();

        $this->repository->save([$post]);
    }

    /**
     * @throws BlogNotFoundException
     */
    #[\Override]
    public function draft(int $postId): void
    {
        if (($post = $this->postQueryService->getPost($postId)) === null) {
            throw new BlogNotFoundException('This post does not exist!');
        }

        $post->toDraft();

        $this->repository->save([$post]);
    }

    /**
     * @throws BlogNotFoundException
     */
    #[\Override]
    public function moderate(int $postId, PostModerateDTO $postModerateDTO): void
    {
        if (($post = $this->postQueryService->getPost($postId)) === null) {
            throw new BlogNotFoundException('This post does not exist!');
        }

        $post->edit($postModerateDTO->getTitle(), $postModerateDTO->getContent());
        $post->resetTags();

        $postTags = $postModerateDTO->getTags();
        foreach ($postTags as $tag) {
            $post->addTag($this->tagRepository->getOrCreate($tag));
        }

        if ($postModerateDTO->isPublic()) {
            $post->isPublic() ?: $post->publish();
        } else {
            $post->toDraft();
        }

        $this->repository->save([$post]);
    }

    /**
     * @throws BlogNotFoundException
     */
    #[\Override]
    public function delete(int $postId): void
    {
        if (($post = $this->postQueryService->getPost($postId)) === null) {
            throw new BlogNotFoundException('This post does not exist!');
        }

        $this->repository->delete([$post]);
    }

}
