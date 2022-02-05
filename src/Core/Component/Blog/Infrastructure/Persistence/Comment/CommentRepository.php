<?php

declare(strict_types=1);

namespace  App\Core\Component\Blog\Infrastructure\Persistence\Comment;

use App\Core\Component\Blog\Domain\Comment;
use App\Core\Component\Blog\Domain\Port\CommentRepositoryInterface;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\Select;
use Cycle\ORM\Transaction;

final class CommentRepository extends Select\Repository implements CommentRepositoryInterface
{
    private Transaction $transaction;

    public function __construct(Select $select, ORMInterface $orm)
    {
        parent::__construct($select);
        $this->transaction = new Transaction($orm);
    }

    public function getPublicComment(int $commentId): ?Comment
    {
        return $this->findOne(['id' => $commentId, 'public' => 1]);
    }

    public function getComment(int $commentId): ?Comment
    {
        return $this->findOne(['id' => $commentId]);
    }

    public function save(array $comments): void
    {
        foreach ($comments as $entity) {
            $this->transaction->persist($entity);
        }
        $this->transaction->run();
    }

    public function delete(array $comments): void
    {
        foreach ($comments as $entity) {
            $this->transaction->delete($entity);
        }
        $this->transaction->run();
    }
}
