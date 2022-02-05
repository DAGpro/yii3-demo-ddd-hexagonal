<?php

declare(strict_types=1);

namespace App\Core\Component\Blog\Application\Service\AppService\CommandService;

use App\Core\Component\Blog\Application\Service\CommandService\AuthorPostServiceInterface;
use App\Core\Component\Blog\Application\Service\CommandService\PostChangeDTO;
use App\Core\Component\Blog\Application\Service\CommandService\PostCreateDTO;
use App\Core\Component\Blog\Application\Service\QueryService\AuthorPostQueryServiceInterface;
use App\Core\Component\Blog\Domain\Exception\BlogNotFoundException;
use App\Core\Component\Blog\Domain\Port\PostRepositoryInterface;
use App\Core\Component\Blog\Domain\Port\TagRepositoryInterface;
use App\Core\Component\Blog\Domain\Post;
use App\Core\Component\Blog\Domain\User\Author;

final class AuthorPostService implements AuthorPostServiceInterface
{
    private PostRepositoryInterface $repository;
    private TagRepositoryInterface $tagRepository;
    private AuthorPostQueryServiceInterface $postQueryService;

    public function __construct(
        PostRepositoryInterface $repository,
        AuthorPostQueryServiceInterface $postQueryService,
        TagRepositoryInterface  $tagRepository
    ) {
        $this->repository = $repository;
        $this->tagRepository = $tagRepository;
        $this->postQueryService = $postQueryService;
    }

    public function create(PostCreateDTO $postCreateDTO, Author $author): void
    {
        $post = new Post(
            $postCreateDTO->getTitle(),
            $postCreateDTO->getContent(),
            $author
        );

        foreach ($postCreateDTO->getTags() as $tag) {
            $post->addTag($this->tagRepository->getOrCreate($tag));
        }

        $this->repository->save([$post]);
    }

    /**
     * @throws BlogNotFoundException
     */
    public function edit(string $postSlug, PostChangeDTO $postChangeDTO): void
    {
        if (($post = $this->postQueryService->getPostBySlug($postSlug)) === null) {
            throw new BlogNotFoundException('This post does not exist!');
        }
        $post->editPost($postChangeDTO->getTitle(), $postChangeDTO->getContent());
        $post->resetTags();

        $postTags = $postChangeDTO->getTags();
        foreach ($postTags as $tag) {
            $post->addTag($this->tagRepository->getOrCreate($tag));
        }

        $this->repository->save([$post]);
    }

    /**
     * @throws BlogNotFoundException
     */
    public function delete(string $postSlug): void
    {
        if (($post = $this->postQueryService->getPostBySlug($postSlug)) === null) {
            throw new BlogNotFoundException('This post does not exist!');
        }
        $this->repository->delete([$post]);
    }

}
