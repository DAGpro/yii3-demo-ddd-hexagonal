<?php

declare(strict_types=1);

namespace App\Blog\Infrastructure\Persistence\Comment\Scope;

use Cycle\ORM\Select\ScopeInterface as ConstrainInterface;
use Cycle\ORM\Select\QueryBuilder;

/**
 * Not deleted
 * Public with condition
 * Sorted
 */
final readonly class PublicScope implements ConstrainInterface
{
    public function __construct(private ?array $publicOrCondition = null)
    {
    }

    #[\Override]
    public function apply(QueryBuilder $query): void
    {
        // public or...
        if ($this->publicOrCondition !== null) {
            $query->where([
                '@or' => [
                    ['public' => true],
                    $this->publicOrCondition,
                ],
            ]);
        } else {
            $query->andWhere('public', '=', true);
        }
        // sort
        $query->orderBy('published_at', 'DESC');
    }
}
