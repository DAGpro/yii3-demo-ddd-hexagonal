<?php

declare(strict_types=1);

namespace App\Core\Component\Blog\Infrastructure\Persistence\Post\Scope;

use Cycle\ORM\Select\QueryBuilder;
use Cycle\ORM\Select\ScopeInterface;

final class PublicAndNotDeletedConstrain implements ScopeInterface
{
    public function apply(QueryBuilder $query)
    {
        $query->where('deleted_at', '=', null)->andWhere('public' , '=', 1);
    }
}
