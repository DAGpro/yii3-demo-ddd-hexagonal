<?php

declare(strict_types=1);

namespace App\Core\Component\Blog\Infrastructure\Persistence\Post\Scope;

use Cycle\ORM\Select\ConstrainInterface;
use Cycle\ORM\Select\QueryBuilder;

final class PublicScope implements ConstrainInterface
{
    public function apply(QueryBuilder $query): void
    {
        // public only
        $query->where(['public' => true]);
    }
}
