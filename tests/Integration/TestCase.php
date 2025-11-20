<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use Codeception\Test\Unit;
use Cycle\Database\DatabaseInterface;
use Cycle\ORM\ORMInterface;
use Override;
use Psr\Container\ContainerInterface;
use Yiisoft\Config\Config;
use Yiisoft\Config\ConfigInterface;
use Yiisoft\Config\ConfigPaths;
use Yiisoft\Config\Modifier\RecursiveMerge;
use Yiisoft\Config\Modifier\ReverseMerge;
use Yiisoft\Di\Container;
use Yiisoft\Di\ContainerConfig;

class TestCase extends Unit
{
    protected static ?ContainerInterface $container = null;
    protected static ?DatabaseInterface $database = null;
    protected static ?ORMInterface $orm = null;

    #[Override]
    protected function _before(): void
    {
        $this->initializeContainer();
    }

    #[Override]
    protected function _after(): void
    {
        $this->rollbackTransaction();
    }

    protected function initializeContainer(): void
    {
        if (self::$container === null) {
            $config = new Config(
                new ConfigPaths(
                    dirname(__DIR__, 2),
                    'config',
                    'vendor',
                ),
                'test',
                [
                    ReverseMerge::groups('events-web', 'events'),
                    RecursiveMerge::groups('params-web', 'params', 'events-web', 'events'),
                ],
                'params-web',
            );

            self::$container = new Container(
                ContainerConfig::create()
                    ->withDefinitions(
                        array_merge(
                            $config->get('di-web'),
                            [
                                ConfigInterface::class => $config,
                            ],
                        ),
                    ),
            );

            self::$orm = self::$container->get(ORMInterface::class);
        }
    }

    protected function beginTransaction(): void
    {
        self::$database?->begin();
    }

    protected function rollbackTransaction(): void
    {
        if (self::$database !== null && self::$database->getDriver()->getTransactionLevel() > 0) {
            self::$database->rollback();
        }
    }
}
