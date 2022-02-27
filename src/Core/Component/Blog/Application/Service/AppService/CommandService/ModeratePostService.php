<?php

declare(strict_types=1);

namespace App\Core\Component\Blog\Application\Service\AppService\CommandService;

use App\Core\Component\Blog\Application\Service\CommandService\ModeratePostServiceInterface;
use App\Core\Component\Blog\Application\Service\CommandService\PostModerateDTO;
use App\Core\Component\Blog\Application\Service\QueryService\ModeratePostQueryServiceInterface;
use App\Core\Component\Blog\Domain\Exception\BlogNotFoundException;
use App\Core\Component\Blog\Domain\Port\PostRepositoryInterface;
use App\Core\Component\Blog\Domain\Port\TagRepositoryInterface;

final class ModeratePostService implements ModeratePostServiceInterface
{
    private PostRepositoryInterface $repository;
    private TagRepositoryInterface $tagRepository;
    private ModeratePostQueryServiceInterface $postQueryService;

    public function __construct(
        ModeratePostQueryServiceInterface $postQueryService,
        PostRepositoryInterface $repository,
        TagRepositoryInterface  $tagRepository
    ) {
        $this->repository = $repository;
        $this->tagRepository = $tagRepository;
        $this->postQueryService = $postQueryService;
    }

    /**
     * @throws BlogNotFoundException
     */
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
            !$post->isPublic() ?: $post->publish();
        } else {
            $post->toDraft();
        }


        $this->repository->save([$post]);
    }

    /**
     * @throws BlogNotFoundException
     */
    public function delete(int $postId): void
    {
        if (($post = $this->postQueryService->getPost($postId)) === null) {
            throw new BlogNotFoundException('This post does not exist!');
        }

        $this->repository->delete([$post]);
    }

}
