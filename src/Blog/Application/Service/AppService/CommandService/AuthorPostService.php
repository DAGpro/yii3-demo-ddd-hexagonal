<?php

declare(strict_types=1);

namespace App\Blog\Application\Service\AppService\CommandService;

use App\Blog\Application\Service\CommandService\AuthorPostServiceInterface;
use App\Blog\Application\Service\CommandService\PostChangeDTO;
use App\Blog\Application\Service\CommandService\PostCreateDTO;
use App\Blog\Application\Service\QueryService\AuthorPostQueryServiceInterface;
use App\Blog\Domain\Exception\BlogAccessDeniedException;
use App\Blog\Domain\Exception\BlogNotFoundException;
use App\Blog\Domain\Port\PostRepositoryInterface;
use App\Blog\Domain\Port\TagRepositoryInterface;
use App\Blog\Domain\Post;
use App\Blog\Domain\User\Author;
use Override;

final readonly class AuthorPostService implements AuthorPostServiceInterface
{
    public function __construct(
        private PostRepositoryInterface $repository,
        private AuthorPostQueryServiceInterface $postQueryService,
        private TagRepositoryInterface $tagRepository,
    ) {}

    #[Override]
    public function create(PostCreateDTO $postCreateDTO, Author $author): void
    {
        $post = new Post(
            $postCreateDTO->getTitle(),
            $postCreateDTO->getContent(),
            $author,
        );

        foreach ($postCreateDTO->getTags() as $tag) {
            $post->addTag($this->tagRepository->getOrCreate($tag));
        }

        $this->repository->save([$post]);
    }

    /**
     * @throws BlogNotFoundException
     * @throws BlogAccessDeniedException
     */
    #[Override]
    public function edit(
        string $postSlug,
        PostChangeDTO $postChangeDTO,
        Author $author,
    ): void {
        if (($post = $this->postQueryService->getPostBySlug($postSlug)) === null) {
            throw new BlogNotFoundException('This post does not exist!');
        }

        if (!$post->getAuthor()->isEqual($author)) {
            throw new BlogAccessDeniedException('You are not the author of this post!');
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
     * @throws BlogAccessDeniedException
     */
    #[Override]
    public function delete(string $postSlug, Author $author): void
    {
        if (($post = $this->postQueryService->getPostBySlug($postSlug)) === null) {
            throw new BlogNotFoundException('This post does not exist!');
        }

        if (!$post->isAuthor($author)) {
            throw new BlogAccessDeniedException('You are not the author of this post!');
        }

        $this->repository->delete([$post]);
    }

}
