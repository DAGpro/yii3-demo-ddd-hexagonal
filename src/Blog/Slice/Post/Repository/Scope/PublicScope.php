<?php

declare(strict_types=1);

namespace App\Blog\Slice\Post\Repository\Scope;

use Cycle\ORM\Select\QueryBuilder;
use Cycle\ORM\Select\ScopeInterface as ConstrainInterface;
use Override;

final class PublicScope implements ConstrainInterface
{
    #[Override]
    public function apply(QueryBuilder $query): void
    {
        // public only
        $query->where(['public' => true]);
    }
}
