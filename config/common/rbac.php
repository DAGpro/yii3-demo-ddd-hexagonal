<?php

declare(strict_types=1);

use Yiisoft\Access\AccessCheckerInterface;
use Yiisoft\Rbac\Manager;
use Yiisoft\Rbac\Php\AssignmentsStorage;
use Yiisoft\Rbac\Php\ItemsStorage;
use Yiisoft\Rbac\AssignmentsStorageInterface;
use Yiisoft\Rbac\ItemsStorageInterface;
use Yiisoft\Rbac\RuleFactoryInterface;

/** @var array $params */

return [
    ItemsStorageInterface::class => [
        'class' => ItemsStorage::class,
        '__construct()' => [
            'directory' => $params['yiisoft/aliases']['aliases']['@root'] . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'rbac',
        ],
    ],
    AssignmentsStorageInterface::class => [
        'class' => AssignmentsStorage::class,
        '__construct()' => [
            'directory' => $params['yiisoft/aliases']['aliases']['@root'] . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'rbac',
        ],
    ],
    AccessCheckerInterface::class => static function(
        \Psr\Container\ContainerInterface $container
    ) {
        return $container->get(Manager::class);
    },
    Manager::class =>  static function(
        \Psr\Container\ContainerInterface $container
    ) {
        return new  Manager(
            $container->get(ItemsStorageInterface::class),
            $container->get(AssignmentsStorageInterface::class),
            $container->get(RuleFactoryInterface::class),
            true
        );
    }
];
