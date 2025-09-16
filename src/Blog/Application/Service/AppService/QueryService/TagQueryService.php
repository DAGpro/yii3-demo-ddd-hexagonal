<?php

declare(strict_types=1);

namespace App\Blog\Application\Service\AppService\QueryService;

use App\Blog\Application\Service\QueryService\TagQueryServiceInterface;
use App\Blog\Domain\Port\TagRepositoryInterface;
use App\Blog\Domain\Tag;
use Override;
use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Data\Reader\Sort;

final readonly class TagQueryService implements TagQueryServiceInterface
{
    public function __construct(
        private TagRepositoryInterface $tagRepository,
    ) {
    }

    #[Override]
    public function findAllPreloaded(): DataReaderInterface
    {
        return $this->tagRepository
            ->findAllPreloaded()
            ->withSort(
                Sort::only(['id', 'label', 'created_at'])
                    ->withOrder(['created_at' => 'desc']),
            );
    }

    /**
     * @param int<0,max>|null $limit
     */
    #[Override]
    public function getTagMentions(?int $limit = null): DataReaderInterface
    {
        $dataReader = $this->tagRepository
            ->getTagMentions()
            ->withSort(
                Sort::only(['count', 'label'])
                    ->withOrder(['count' => 'desc']),
            );

        if ($limit !== null) {
            return $dataReader->withLimit($limit);
        }

        /** @var DataReaderInterface $dataReader */
        return $dataReader;
    }

    #[Override]
    public function findByLabel(string $label): ?Tag
    {
        return $this->tagRepository->findByLabel($label);
    }

    #[Override]
    public function getTag(int $tagId): ?Tag
    {
        return $this->tagRepository->getTag($tagId);
    }
}
