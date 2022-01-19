<?php

declare(strict_types=1);

namespace App\Presentation\Backend\Console\Component\IdentityAccess\Access;

use App\Core\Component\IdentityAccess\Access\Application\Service\AssignmentsServiceInterface;
use App\Core\Component\IdentityAccess\User\Application\Service\UserQueryServiceInterface;
use App\Core\Component\IdentityAccess\User\Domain\Exception\IdentityException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Yiisoft\Yii\Console\ExitCode;

class UserAssignmentsCommand extends Command
{
    protected static $defaultName = 'access/userAssignments';

    private AssignmentsServiceInterface $assignmentsService;
    private UserQueryServiceInterface $userQueryService;

    public function __construct(
        AssignmentsServiceInterface $assignmentsService,
        UserQueryServiceInterface $userQueryService
    ) {
        $this->assignmentsService = $assignmentsService;
        $this->userQueryService = $userQueryService;
        parent::__construct();
    }

    public function configure(): void
    {
        $this
            ->setDescription('User assignments list')
            ->setHelp('This command displays a list of user assignments')
            ->addArgument('userId', InputArgument::REQUIRED, 'User id');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $userId = (int)$input->getArgument('userId');

        try {
            $user = $this->userQueryService->getUser($userId);
            if ($user === null) {
                throw new IdentityException('User is not found!');
            }

            $userAssignments =  $this->assignmentsService->getUserAssignments($user);
            if (!$userAssignments->existRoles() && !$userAssignments->existPermissions()) {
                $io->success('The user has no assigned access rights!');
                return ExitCode::OK;
            }

            $io->success('List of user roles and permissions' . $userAssignments->getLogin());
            $this->getUserRolesTable($io, $userAssignments->getRoles());
            $this->getUserPermissionsTable($io, $userAssignments->getPermissions());

            return ExitCode::OK;
        } catch (IdentityException $t) {
            $io->error($t->getMessage());
            return $t->getCode() ?: ExitCode::UNSPECIFIED_ERROR;
        }
    }

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
                ]
            );
        }
        $table->render();
    }

    public function getUserPermissionsTable(OutputInterface $output, array $permissions): void
    {
        if (empty($permissions)) {
            $output->writeln('');
            $output->writeln('User has no assigned permissions!');
            $output->writeln('');
            return;
        }

        $tablePermissions = new Table($output);
        $tablePermissions ->setHeaders(['Permissions']);

        foreach ($permissions as $item) {
            $tablePermissions ->addRow(
                [
                    $item->getName(),
                ]
            );
        }

        $tablePermissions->render();
    }

}
