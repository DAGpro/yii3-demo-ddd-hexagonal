<?php

declare(strict_types=1);

namespace App\Core\Component\Blog\Application;

use App\Core\Component\Blog\Domain\Post;
use App\Core\Component\Blog\Domain\Tag;
use App\Core\Component\Blog\Infrastructure\Persistence\Post\PostRepository;
use App\Core\Component\Blog\Infrastructure\Persistence\Tag\TagRepository;
use App\Core\Component\User\Domain\User;
use App\Presentation\Frontend\Web\Component\Blog\Post\PostForm;

final class PostService
{
    private PostRepository $repository;
    private TagRepository $tagRepository;

    public function __construct(PostRepository $repository, TagRepository $tagRepository)
    {
        $this->repository = $repository;
        $this->tagRepository = $tagRepository;
    }

    public function savePost(User $user, Post $model, PostForm $form): void
    {
        $model->setTitle($form->getTitle());
        $model->setContent($form->getContent());
        $model->resetTags();

        foreach ($form->getTags() as $tag) {
            $model->addTag($this->tagRepository->getOrCreate($tag));
        }

        if ($model->isNewRecord()) {
            $model->setPublic(true);
            $model->setUser($user);
        }

        $this->repository->save($model);
    }

    public function getPostTags(Post $post): array
    {
        return array_map(
            static fn (Tag $tag) => $tag->getLabel(),
            $post->getTags()
        );
    }
}
