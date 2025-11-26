<?php

declare(strict_types=1);

namespace App\Blog\Slice\Post\Repository;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;

/**
 * @psalm-suppress ClassMustBeFinal
 */
#[Entity]
class PostTag
{
    #[Column(type: 'primary')]
    private ?int $id = null;
    private ?int $post_id = null;
    private ?int $tag_id = null;
}
