<?php

declare(strict_types=1);

namespace App\Blog\Domain\Port;

use App\Blog\Domain\Post;
use Cycle\ORM\Select;
use Yiisoft\Data\Reader\DataReaderInterface;

interface PostRepositoryInterface
{
    /**
     * Получить полный архив постов
     *
     * @param int<0, max>|null $limit Ограничение количества записей
     * @return DataReaderInterface Массив с данными архива (год, месяц, количество постов)
     */
    public function getFullArchive(?int $limit = null): DataReaderInterface;

    /**
     * Получить архив постов за указанный месяц
     *
     * @param int $year Год
     * @param int $month Месяц (1-12)
     * @return DataReaderInterface Список постов за указанный месяц
     */
    public function getMonthlyArchive(int $year, int $month): DataReaderInterface;

    /**
     * Получить архив постов за указанный год
     *
     * @param int $year Год
     * @return DataReaderInterface Список постов за указанный год
     */
    public function getYearlyArchive(int $year): DataReaderInterface;


    public function select(): Select;

    /**
     * @param iterable<Post> $posts
     */
    public function save(array $posts): void;

    /**
     * @param iterable<Post> $posts
     */
    public function delete(array $posts): void;
}
