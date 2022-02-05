<?php

declare(strict_types=1);

namespace App\Core\Component\Blog\Domain\Port;

use Cycle\ORM\Select;

interface PostRepositoryInterface
{

    public function select(): Select;

    public function save(array $posts): void;

    public function delete(array $posts): void;
}
