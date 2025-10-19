<?php

declare(strict_types=1);

use Yiisoft\Access\AccessCheckerInterface;
use Yiisoft\Definitions\Reference;
use Yiisoft\Rbac\AssignmentsStorageInterface;
use Yiisoft\Rbac\ItemsStorageInterface;
use Yiisoft\Rbac\Manager;
use Yiisoft\Rbac\Php\AssignmentsStorage;
use Yiisoft\Rbac\Php\ItemsStorage;

/** @var array $params */

return [
    Manager::class => [
        'class' => Manager::class,
        '__construct()' => [
            'itemsStorage' => Reference::to(ItemsStorageInterface::class),
            'assignmentsStorage' => Reference::to(AssignmentsStorageInterface::class),
            'enableDirectPermissions' => true,
        ],
    ],
    ItemsStorageInterface::class => [
        'class' => ItemsStorage::class,
        '__construct()' => [
            'filePath' => $params['yiisoft/aliases']['aliases']['@root'] . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'rbac' . DIRECTORY_SEPARATOR . 'items.php',
        ],
    ],
    AssignmentsStorageInterface::class => [
        'class' => AssignmentsStorage::class,
        '__construct()' => [
            'filePath' => $params['yiisoft/aliases']['aliases']['@root'] . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'rbac' . DIRECTORY_SEPARATOR . 'assignments.php',
        ],
    ],
    AccessCheckerInterface::class => Manager::class,
];
