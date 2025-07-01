<?php

declare(strict_types=1);

namespace App\IdentityAccess\Presentation\Backend\Console\Access;

use App\IdentityAccess\Access\Application\Service\AccessRightsServiceInterface;
use App\IdentityAccess\Access\Application\Service\RoleDTO;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Yiisoft\Yii\Console\ExitCode;

#[AsCommand(
    'access:viewRole',
    'View role and child roles and permissions',
    help: 'This command shows a role, child roles and permissions'
)]
final class ViewRoleCommand extends Command
{
    public function __construct(
        private readonly AccessRightsServiceInterface $accessRightsService,
    ) {
        parent::__construct();
    }

    #[\Override]
    public function configure(): void
    {
        $this->addArgument('role', InputArgument::REQUIRED, 'View role and child roles and permissions');
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $roleName = $input->getArgument('role');
        $role = $this->accessRightsService->getRoleByName($roleName);

        if (!$role) {
            $io->error('This role does not exist!');
            return ExitCode::OK;
        }

        $io->success('Access rights list!');
        $this->getRoleTable($output, $role);
        return ExitCode::OK;
    }

    private function getRoleTable(OutputInterface $output, RoleDTO $role): void
    {
        $table = new Table($output);
        $table->setHeaders(['Role', 'Child Roles', 'Nested Roles', 'Nested Permissions', 'Child Permissions']);
        $table->setColumnMaxWidth(4, 20);
        $table->setColumnMaxWidth(3, 20);
        $table->setColumnMaxWidth(2, 18);
        $table->setColumnMaxWidth(1, 15);

        $table->addRow(
            [
                $role->getName(),
                $role->getChildRolesName(),
                $role->getNestedRolesName(),
                $role->getNestedPermissionsName(),
                $role->getChildPermissionsName(),
            ],
        );

        $table->render();
    }
}
