<?php

declare(strict_types=1);

namespace App\Core\Component\Blog\Application\Service\AppService\CommandService;

use App\Core\Component\Blog\Application\Service\CommandService\TagServiceInterface;
use App\Core\Component\Blog\Domain\Exception\BlogNotFoundException;
use App\Core\Component\Blog\Domain\Port\TagRepositoryInterface;

final class TagService implements TagServiceInterface
{
    private TagRepositoryInterface $repository;

    public function __construct(TagRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @throws BlogNotFoundException
     */
    public function changeTag(int $tagId, string $tagLabel): void
    {
        if (($tag = $this->repository->getTag($tagId)) === null) {
            throw new BlogNotFoundException('This tag does not exist!');
        }
        $tag->change($tagLabel);
        $this->repository->save([$tag]);
    }

    /**
     * @throws BlogNotFoundException
     */
    public function delete(int $tagId): void
    {
        if (($tag = $this->repository->getTag($tagId)) === null) {
            throw new BlogNotFoundException('This tag does not exist!');
        }
        $this->repository->delete([$tag]);
    }
}
