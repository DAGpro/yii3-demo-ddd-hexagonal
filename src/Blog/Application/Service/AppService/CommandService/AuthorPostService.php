<?php

declare(strict_types=1);

namespace App\Blog\Application\Service\AppService\CommandService;

use App\Blog\Application\Service\CommandService\AuthorPostServiceInterface;
use App\Blog\Application\Service\CommandService\PostChangeDTO;
use App\Blog\Application\Service\CommandService\PostCreateDTO;
use App\Blog\Application\Service\QueryService\AuthorPostQueryServiceInterface;
use App\Blog\Domain\Exception\BlogNotFoundException;
use App\Blog\Domain\Port\PostRepositoryInterface;
use App\Blog\Domain\Port\TagRepositoryInterface;
use App\Blog\Domain\Post;
use App\Blog\Domain\User\Author;

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
        $post->edit($postChangeDTO->getTitle(), $postChangeDTO->getContent());
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
