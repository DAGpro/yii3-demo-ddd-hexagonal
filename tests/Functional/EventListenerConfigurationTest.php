<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Yiisoft\Config\Config;
use Yiisoft\Config\ConfigPaths;
use Yiisoft\Config\Modifier\RecursiveMerge;
use Yiisoft\Config\Modifier\ReverseMerge;
use Yiisoft\Di\Container;
use Yiisoft\Di\ContainerConfig;
use Yiisoft\Yii\Event\ListenerConfigurationChecker;

use function dirname;

class EventListenerConfigurationTest extends TestCase
{
    public function testConsoleListenerConfiguration(): void
    {
        $config = new Config(
            new ConfigPaths(dirname(__DIR__, 2), 'config', 'vendor'),
            null,
            [
                ReverseMerge::groups('events-console', 'events'),
                RecursiveMerge::groups('params-console', 'events'),
                ...[],
            ],
            'params-console',
        );

        $containerConfig = ContainerConfig::create()
            ->withDefinitions($config->get('di-console'));
        $container = new Container($containerConfig)->get(ContainerInterface::class);
        $checker = $container->get(ListenerConfigurationChecker::class);
        $checker->check($config->get('events-console'));

        self::assertInstanceOf(ListenerConfigurationChecker::class, $checker);
    }
}
