<?php

declare(strict_types=1);

namespace App\Infrastructure\Authentication;

use Cycle\ORM\Select;
use Throwable;
use Yiisoft\Auth\IdentityRepositoryInterface;
use Yiisoft\Yii\Cycle\Data\Writer\EntityWriter;

final class IdentityRepository extends Select\Repository implements IdentityRepositoryInterface
{
    private EntityWriter $entityWriter;

    public function __construct(Select $select, EntityWriter $entityWriter)
    {
        $this->entityWriter = $entityWriter;
        parent::__construct($select);
    }

    /**
     * @param string $id
     *
     * @return Identity|null
     */
    public function findIdentity(string $id): ?Identity
    {
        return $this->findOne(['id' => $id]);
    }

    public function findByUserId(int $userId): ?Identity
    {
        return $this->findOne(['user_id' => $userId]);
    }

    /**
     * @throws Throwable
     */
    public function save(Identity $identity): void
    {
        $this->entityWriter->write([$identity]);
    }
}
