<?php

declare(strict_types=1);

namespace App\IdentityAccess\Access\Slice\Controller\Backend\Console;

use App\IdentityAccess\Access\Slice\Service\AccessRightsServiceInterface;
use App\IdentityAccess\Access\Slice\Service\PermissionDTO;
use App\IdentityAccess\Access\Slice\Service\RoleDTO;
use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Yiisoft\Yii\Console\ExitCode;

#[AsCommand(
    'access:list',
    'List of access rights with children',
    help: 'This command shows a list of permissions with children',
)]
final class AccessListCommand extends Command
{
    public function __construct(
        private readonly AccessRightsServiceInterface $accessRightsService,
    ) {
        parent::__construct();
    }


    #[Override]
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

    /**
     * @param array<RoleDTO> $roles
     */
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
                ],
            );

            if (++$i !== $count) {
                $table->addRow(new TableSeparator());
            }
        }

        $table->render();
    }

    /**
     * @param array<PermissionDTO> $permissions
     */
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
