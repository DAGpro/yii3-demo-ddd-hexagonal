<?php

declare(strict_types=1);

namespace App\Blog\Infrastructure\Persistence\Post\Scope;

use Cycle\ORM\Select\QueryBuilder;
use Cycle\ORM\Select\ScopeInterface;

final class PublicAndNotDeletedConstrain implements ScopeInterface
{
    #[\Override]
    public function apply(QueryBuilder $query): void
    {
        $query->where('deleted_at', '=', null)->andWhere('public', '=', 1);
    }
}
