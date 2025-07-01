<?php

declare(strict_types=1);

namespace App\IdentityAccess\Presentation\Backend\Console\Access;

use App\IdentityAccess\Access\Application\Service\AssignmentsServiceInterface;
use App\IdentityAccess\Access\Application\Service\UserAssignmentsDTO;
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
    'assignments:list',
    'All assignments list',
    help: 'This command displays a list of all assignments',
)]
final class AssignmentsListCommand extends Command
{
    public function __construct(
        private readonly AssignmentsServiceInterface $assignmentsService,
    ) {
        parent::__construct();
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $userAssignments = $this->assignmentsService->getAssignments();
        if (empty($userAssignments)) {
            $io->error('Users have no assigned access rights!');
            return ExitCode::OK;
        }

        $io->success('List of users roles and permissions');
        $this->getAssignmentsTable($io, $userAssignments);

        return ExitCode::OK;
    }

    private function getAssignmentsTable(OutputInterface $output, array $roles): void
    {
        $table = new Table($output);
        $table->setHeaders(['UserId', 'Login', 'Roles ', 'Child roles', 'Child Permissions', 'Permissions']);
        $table->setColumnMaxWidth(5, 25);
        $table->setColumnMaxWidth(4, 25);
        $table->setColumnMaxWidth(3, 18);
        $table->setColumnMaxWidth(2, 15);
        $table->setColumnMaxWidth(1, 15);
        $count = count($roles);
        $i = 0;
        /** @var UserAssignmentsDTO $item */
        foreach ($roles as $item) {
            $table->addRow(
                [
                    $item->getId(),
                    $item->getLogin(),
                    $item->getRolesName(),
                    $item->getChildRolesName(),
                    $item->getChildPermissionsName(),
                    $item->getPermissionsName(),
                ],
            );
            if (++$i !== $count) {
                $table->addRow(new TableSeparator());
            }
        }
        $table->render();
    }

}
