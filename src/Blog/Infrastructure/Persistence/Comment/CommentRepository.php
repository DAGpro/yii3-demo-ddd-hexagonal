<?php

declare(strict_types=1);

namespace  App\Blog\Infrastructure\Persistence\Comment;

use App\Blog\Domain\Comment;
use App\Blog\Domain\Port\CommentRepositoryInterface;
use Cycle\ORM\EntityManager;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\Select;

final class CommentRepository extends Select\Repository implements CommentRepositoryInterface
{
    private EntityManager $entityManager;

    public function __construct(Select $select, ORMInterface $orm)
    {
        parent::__construct($select);
        $this->entityManager = new EntityManager($orm);
    }

    public function getPublicComment(int $commentId): ?Comment
    {
        return $this->findOne(['id' => $commentId]);
    }

    public function getComment(int $commentId): ?Comment
    {
        return $this->select()->scope()->where(['id' => $commentId])->fetchOne();
    }

    public function save(array $comments): void
    {
        foreach ($comments as $entity) {
            $this->entityManager->persist($entity);
        }
        $this->entityManager->run();
    }

    public function delete(array $comments): void
    {
        foreach ($comments as $entity) {
            $this->entityManager->delete($entity);
        }
        $this->entityManager->run();
    }
}
