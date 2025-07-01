<?php

declare(strict_types=1);

namespace App\Blog\Infrastructure\Persistence\Comment;

use App\Blog\Domain\Comment;
use App\Blog\Domain\Port\CommentRepositoryInterface;
use Cycle\ORM\EntityManagerInterface;
use Cycle\ORM\Select;

final class CommentRepository extends Select\Repository implements CommentRepositoryInterface
{
    public function __construct(
        protected Select $select,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct($select);
    }

    #[\Override]
    public function getPublicComment(int $commentId): ?Comment
    {
        return $this->findOne(['id' => $commentId]);
    }

    #[\Override]
    public function getComment(int $commentId): ?Comment
    {
        return $this->select()->scope()->where(['id' => $commentId])->fetchOne();
    }

    #[\Override]
    public function save(array $comments): void
    {
        foreach ($comments as $entity) {
            $this->entityManager->persist($entity);
        }
        $this->entityManager->run();
    }

    #[\Override]
    public function delete(array $comments): void
    {
        foreach ($comments as $entity) {
            $this->entityManager->delete($entity);
        }
        $this->entityManager->run();
    }
}
