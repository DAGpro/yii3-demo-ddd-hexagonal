<?php

declare(strict_types=1);

namespace App\Blog\Domain\Port;

use Cycle\Database\Injection\FragmentInterface;
use Cycle\ORM\Select;

interface PostRepositoryInterface
{
    /**
     * @param 'day'|'month'|'year' $attr
     * @return FragmentInterface
     */
    public function extractFromDateColumn(string $attr): FragmentInterface;

    public function select(): Select;

    public function save(array $posts): void;

    public function delete(array $posts): void;
}
