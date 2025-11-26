<?php

declare(strict_types=1);

namespace App\IdentityAccess\Access\Slice\Controller\Backend\Console;

use App\IdentityAccess\Access\Slice\Service\AssignmentsServiceInterface;
use App\IdentityAccess\Access\Slice\Service\PermissionDTO;
use App\IdentityAccess\Access\Slice\Service\RoleDTO;
use App\IdentityAccess\User\Domain\Exception\IdentityException;
use App\IdentityAccess\User\Slice\User\UserQueryServiceInterface;
use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Yiisoft\Yii\Console\ExitCode;

#[AsCommand(
    'assignments:user',
    'User assignments list',
    help: 'This command displays a list of user assignments',
)]
final class UserAssignmentsCommand extends Command
{
    public function __construct(
        private readonly AssignmentsServiceInterface $assignmentsService,
        private readonly UserQueryServiceInterface $userQueryService,
    ) {
        parent::__construct();
    }

    #[Override]
    public function configure(): void
    {
        $this->addArgument('userId', InputArgument::REQUIRED, 'User id');
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $userId = (int) $input->getArgument('userId');

        try {
            $user = $this->userQueryService->getUser($userId);
            if ($user === null) {
                throw new IdentityException('User is not found!');
            }

            $userAssignments = $this->assignmentsService->getUserAssignments($user);
            if (!$userAssignments->existRoles() && !$userAssignments->existPermissions()) {
                $io->success('The user has no assigned access rights!');
                return ExitCode::OK;
            }

            $io->success('List of user roles and permissions: ' . $userAssignments->getLogin());
            $this->getUserRolesTable($io, $userAssignments->getRoles());
            $this->getUserPermissionsTable($io, $userAssignments->getPermissions());

            return ExitCode::OK;
        } catch (IdentityException $t) {
            $io->error($t->getMessage());
            return $t->getCode() ?: ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * @param array<PermissionDTO> $permissions
     */
    private function getUserPermissionsTable(OutputInterface $output, array $permissions): void
    {
        if (empty($permissions)) {
            $output->writeln('');
            $output->writeln('User has no assigned permissions!');
            $output->writeln('');
            return;
        }

        $tablePermissions = new Table($output);
        $tablePermissions->setHeaders(['Permissions']);

        foreach ($permissions as $item) {
            $tablePermissions->addRow(
                [
                    $item->getName(),
                ],
            );
        }

        $tablePermissions->render();
    }

    /**
     * @param array<RoleDTO> $roles
     */
    private function getUserRolesTable(OutputInterface $output, array $roles): void
    {
        if (empty($roles)) {
            $output->writeln('');
            $output->writeln('User has no assigned roles!');
            $output->writeln('');
            return;
        }

        $table = new Table($output);
        $table->setHeaders(['Role', 'Child Roles', 'Nested Roles', 'Nested Permissions', 'Child Permissions']);
        $table->setColumnMaxWidth(4, 25);
        $table->setColumnMaxWidth(3, 25);
        $table->setColumnMaxWidth(2, 18);
        $table->setColumnMaxWidth(1, 15);
        foreach ($roles as $item) {
            $table->addRow(
                [
                    $item->getName(),
                    $item->getChildRolesName(),
                    $item->getNestedRolesName(),
                    $item->getNestedPermissionsName(),
                    $item->getChildPermissionsName(),
                ],
            );
        }
        $table->render();
    }

}
