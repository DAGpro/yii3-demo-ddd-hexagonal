<?php

declare(strict_types=1);

namespace App\Site\Presentation\Backend\Console\Router;

use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Yiisoft\Router\Route;
use Yiisoft\Router\RouteCollectionInterface;
use Yiisoft\Yii\Console\ExitCode;

#[AsCommand(
    name: 'router:list',
    description: 'List all registered routes',
    help: 'This command displays a list of registered routes.',
)]
final class ListCommand extends Command
{
    public function __construct(private readonly RouteCollectionInterface $routeCollection)
    {
        parent::__construct();
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $table = new Table($output);
        $routes = $this->routeCollection->getRoutes();
        uasort(
            $routes,
            static fn(Route $a, Route $b) => ($a->getData('host') <=> $b->getData('host')) ?: ($a->getData(
                'name',
            ) <=> $b->getData('name')),
        );
        $table->setHeaders(['Host', 'Methods', 'Name', 'Pattern', 'Defaults']);
        foreach ($routes as $route) {
            $table->addRow(
                [
                    $route->getData('host'),
                    implode(',', $route->getData('methods')),
                    $route->getData('name'),
                    $route->getData('pattern'),
                    implode(',', $route->getData('defaults')),
                ],
            );
            if (next($routes) !== false) {
                $table->addRow(new TableSeparator());
            }
        }

        $table->render();
        return ExitCode::OK;
    }
}
