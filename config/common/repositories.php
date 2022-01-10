<?php

declare(strict_types=1);


use App\Core\Component\IdentityAccess\User\Domain\Port\UserRepositoryInterface;
use App\Core\Component\IdentityAccess\User\Domain\User;
use App\Infrastructure\Authentication\Identity;
use Cycle\ORM\ORMInterface;
use Psr\Container\ContainerInterface;
use Yiisoft\Auth\IdentityRepositoryInterface;

return [
    UserRepositoryInterface::class => static function (ContainerInterface $container) {
        return $container->get(ORMInterface::class)->getRepository(User::class);
    },

    IdentityRepositoryInterface::class => static function (ContainerInterface $container): IdentityRepositoryInterface {
        return $container->get(ORMInterface::class)->getRepository(Identity::class);
    },
];
