<?php

declare(strict_types=1);

namespace App\Blog\Application\Service\AppService\QueryService;

use App\Blog\Application\Service\QueryService\TagQueryServiceInterface;
use App\Blog\Domain\Port\TagRepositoryInterface;
use App\Blog\Domain\Tag;
use Cycle\ORM\Select;
use Override;
use Yiisoft\Data\Cycle\Reader\EntityReader;
use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Data\Reader\Sort;

final readonly class TagQueryService implements TagQueryServiceInterface
{
    public function __construct(private TagRepositoryInterface $tagRepository)
    {
    }

    #[Override]
    public function findAllPreloaded(): DataReaderInterface
    {
        $query = $this->tagRepository->select();
        return $this->prepareDataReader($query);
    }

    #[Override]
    public function getTagMentions(?int $limit = null): DataReaderInterface
    {
        $select = $this->tagRepository->getTagMentions();

        $sort = Sort::only(['count', 'label'])->withOrder(['count' => 'desc']);
        $dataReader = new EntityReader($select)->withSort($sort);

        if (!$limit) {
            return $dataReader->withLimit($limit);
        }

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

    private function prepareDataReader(Select $query): EntityReader
    {
        return new EntityReader($query)->withSort(
            Sort::only(['id', 'label', 'created_at'])
                ->withOrder(['created_at' => 'desc']),
        );
    }

}
