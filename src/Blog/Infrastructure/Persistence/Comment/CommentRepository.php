<?php

declare(strict_types=1);

namespace App\Blog\Infrastructure\Persistence\Comment;

use App\Blog\Domain\Comment;
use App\Blog\Domain\Port\CommentRepositoryInterface;
use Cycle\ORM\EntityManagerInterface;
use Cycle\ORM\Select;
use Override;

/**
 * @extends Select\Repository<Comment>
 */
final class CommentRepository extends Select\Repository implements CommentRepositoryInterface
{
    /**
     * @param Select<Comment> $select
     */
    public function __construct(
        protected Select $select,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct($select);
    }

    #[Override]
    public function getPublicComment(int $commentId): ?Comment
    {
        /** @var Comment|null $comment */
        $comment = $this->findOne(['id' => $commentId]);
        return $comment;
    }

    #[Override]
    public function getComment(int $commentId): ?Comment
    {
        /** @var Comment|null $comment */
        $comment = $this->select()->where(['id' => $commentId])->fetchOne();
        return $comment;
    }

    #[Override]
    /**
     * @param iterable<Comment> $comments
     */
    public function save(iterable $comments): void
    {
        if ($comments === []) {
            return;
        }
        foreach ($comments as $entity) {
            if ($entity instanceof Comment) {
                $this->entityManager->persist($entity);
            }
        }
        $this->entityManager->run();
    }

    #[Override]
    /**
     * @param iterable<Comment> $comments
     */
    public function delete(iterable $comments): void
    {
        if ($comments === []) {
            return;
        }

        foreach ($comments as $entity) {
            if ($entity instanceof Comment) {
                $this->entityManager->delete($entity);
            }
        }
        $this->entityManager->run();
    }
}
