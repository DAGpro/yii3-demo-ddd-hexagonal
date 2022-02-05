<?php

declare(strict_types=1);

namespace App\Core\Component\Blog\Application\Service\AppService\QueryService;

use App\Core\Component\Blog\Application\Service\QueryService\TagQueryServiceInterface;
use App\Core\Component\Blog\Domain\Exception\BlogNotFoundException;
use App\Core\Component\Blog\Domain\Port\TagRepositoryInterface;
use App\Core\Component\Blog\Domain\Tag;
use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Data\Reader\Sort;
use Yiisoft\Yii\Cycle\Data\Reader\EntityReader;

final class TagQueryService implements TagQueryServiceInterface
{
    private TagRepositoryInterface $tagRepository;

    public function __construct(TagRepositoryInterface $tagRepository)
    {
        $this->tagRepository = $tagRepository;
    }

    public function findAllPreloaded(): DataReaderInterface
    {
        $query = $this->tagRepository->select();
        return $this->prepareDataReader($query);
    }

    public function getTagMentions(?int $limit = null): DataReaderInterface
    {
        $select = $this->tagRepository->getTagMentions();

        $sort = Sort::only(['count', 'label'])->withOrder(['count' => 'desc']);
        $dataReader = (new EntityReader($select))->withSort($sort);

        if (!$limit) {
            return $dataReader->withLimit($limit);
        }

        return $dataReader;
    }

    public function findByLabel(string $label): ?Tag
    {
        return $this->tagRepository->findByLabel($label);
    }

    public function getTag(int $tagId): ?Tag
    {
        return $this->tagRepository->getTag($tagId);
    }

    private function prepareDataReader($query): EntityReader
    {
        return (new EntityReader($query))->withSort(
            Sort::only(['id', 'label', 'created_at'])
                ->withOrder(['created_at' => 'desc'])
        );
    }

}
