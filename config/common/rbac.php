<?php

declare(strict_types=1);

use Yiisoft\Access\AccessCheckerInterface;
use Yiisoft\Rbac\Manager;
use Yiisoft\Rbac\Php\AssignmentsStorage;
use Yiisoft\Rbac\Php\RolesStorage;
use Yiisoft\Rbac\RuleFactory\ClassNameRuleFactory;
use Yiisoft\Rbac\RuleFactoryInterface;
use Yiisoft\Rbac\AssignmentsStorageInterface;
use Yiisoft\Rbac\RolesStorageInterface;

/** @var array $params */

return [
    RolesStorageInterface::class => [
        'class' => RolesStorage::class,
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
    RuleFactoryInterface::class => ClassNameRuleFactory::class,
    AccessCheckerInterface::class => static function(
        \Psr\Container\ContainerInterface $container
    ) {
        return $container->get(Manager::class);
    },
    Manager::class =>  static function(
        \Psr\Container\ContainerInterface $container
    ) {
        return new  Manager(
            $container->get(RolesStorageInterface::class),
            $container->get(AssignmentsStorageInterface::class),
            $container->get(RuleFactoryInterface::class),
            true
        );
    }
];
