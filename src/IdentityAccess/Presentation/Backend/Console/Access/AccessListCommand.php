<?php

declare(strict_types=1);

namespace App\IdentityAccess\Presentation\Backend\Console\Access;

use App\IdentityAccess\Access\Application\Service\AccessRightsServiceInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Yiisoft\Yii\Console\ExitCode;

final class AccessListCommand extends Command
{
    protected static $defaultName = 'access/list';

    private AccessRightsServiceInterface $accessRightsService;

    public function __construct(AccessRightsServiceInterface $managementService)
    {
        $this->accessRightsService = $managementService;
        parent::__construct();
    }

    public function configure(): void
    {
        $this
            ->setDescription('List of access rights with children')
            ->setHelp('This command shows a list of permissions with children');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $roles = $this->accessRightsService->getRoles();
        $permissions = $this->accessRightsService->getPermissions();

        if (empty($roles) && empty($permissions)) {
            $io->error('Access rights list is empty!');
            return ExitCode::OK;
        }

        $io->success('Access rights list!');
        $this->getRolesTable($output, $roles);
        $this->getPermissionsTable($output, $permissions);
        return ExitCode::OK;
    }

    private function getRolesTable(OutputInterface $output, array $roles): void
    {
        $table = new Table($output);
        $table->setHeaders(['Role', 'Child Roles', 'Nested Roles', 'Child Permissions', 'Nested Permissions']);
        $table->setColumnMaxWidth(4, 30);
        $table->setColumnMaxWidth(3, 30);
        $table->setColumnMaxWidth(2, 15);
        $table->setColumnMaxWidth(1, 15);
        $count = count($roles);
        $i = 0;
        foreach ($roles as $item) {
            $table->addRow(
                [
                    $item->getName(),
                    $item->getChildRolesName(),
                    $item->getNestedRolesName(),
                    $item->getChildPermissionsName(),
                    $item->getNestedPermissionsName(),
                ]
            );

            if (++$i !== $count) {
                $table->addRow(new TableSeparator());
            }
        }

        $table->render();
    }

    private function getPermissionsTable(OutputInterface $output, array $permissions): void
    {
        $tablePermissions = new Table($output);
        $tablePermissions->setHeaders(['Permissions List']);
        foreach ($permissions as $item) {
            $tablePermissions->addRow([$item->getName()]);
        }

        $tablePermissions->render();
    }
}
