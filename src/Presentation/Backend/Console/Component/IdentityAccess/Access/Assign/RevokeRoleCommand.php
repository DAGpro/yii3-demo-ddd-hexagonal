<?php

declare(strict_types=1);

namespace App\Presentation\Backend\Console\Component\IdentityAccess\Access\Assign;

use App\IdentityAccess\Access\Application\Service\AssignAccessServiceInterface;
use App\IdentityAccess\Access\Application\Service\RoleDTO;
use App\IdentityAccess\Access\Domain\Exception\AssignedItemException;
use App\IdentityAccess\User\Application\Service\UserQueryServiceInterface;
use App\IdentityAccess\User\Domain\Exception\IdentityException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Yiisoft\Yii\Console\ExitCode;

class RevokeRoleCommand extends Command
{
    protected static $defaultName = 'assign/revokeRole';

    private AssignAccessServiceInterface $assigningService;
    private UserQueryServiceInterface $userQueryService;

    public function __construct(
        AssignAccessServiceInterface $assignAccessService,
        UserQueryServiceInterface $userQueryService
    ) {
        $this->assigningService = $assignAccessService;
        parent::__construct();
        $this->userQueryService = $userQueryService;
    }

    public function configure(): void
    {
        $this
            ->setDescription('Revoke RBAC role to given user')
            ->setHelp('This command allows you to revoke RBAC role to user')
            ->addArgument('userId', InputArgument::REQUIRED, 'User id')
            ->addArgument('role', InputArgument::REQUIRED, 'RBAC role');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $roleName = $input->getArgument('role');
        $userId = $input->getArgument('userId');

        try {
            $user = $this->userQueryService->getUser((int)$userId);
            if ($user === null) {
                throw new IdentityException('User is not found!');
            }

            $role = new RoleDTO($roleName);
            $this->assigningService->revokeRole($role, $user->getId());

            $io->success('Role was revoke to given user');
        } catch (AssignedItemException|IdentityException $t) {
            $io->error($t->getMessage());
            return $t->getCode() ?: ExitCode::UNSPECIFIED_ERROR;
        }

        return ExitCode::OK;
    }
}
