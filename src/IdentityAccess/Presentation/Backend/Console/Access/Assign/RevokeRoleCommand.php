<?php

declare(strict_types=1);

namespace App\IdentityAccess\Presentation\Backend\Console\Access\Assign;

use App\IdentityAccess\Access\Application\Service\AssignAccessServiceInterface;
use App\IdentityAccess\Access\Application\Service\RoleDTO;
use App\IdentityAccess\Access\Domain\Exception\AssignedItemException;
use App\IdentityAccess\User\Application\Service\UserQueryServiceInterface;
use App\IdentityAccess\User\Domain\Exception\IdentityException;
use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Yiisoft\Yii\Console\ExitCode;

#[AsCommand(
    'assign:revokeRole',
    'Revoke RBAC role to given user',
    help: 'This command allows you to revoke RBAC role to user',
)]
final class RevokeRoleCommand extends Command
{
    public function __construct(
        private readonly AssignAccessServiceInterface $assignAccessService,
        private readonly UserQueryServiceInterface $userQueryService,
    ) {
        parent::__construct();
    }

    #[Override]
    public function configure(): void
    {
        $this
            ->addArgument('userId', InputArgument::REQUIRED, 'User id')
            ->addArgument('role', InputArgument::REQUIRED, 'RBAC role');
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $roleName = (string) $input->getArgument('role');
        $userId = (string) $input->getArgument('userId');

        try {
            $user = $this->userQueryService->getUser((int) $userId);
            if ($user === null || ($userId = $user->getId()) === null) {
                throw new IdentityException('User is not found!');
            }

            $role = new RoleDTO($roleName);
            $this->assignAccessService->revokeRole($role, $userId);

            $io->success('Role was revoke to given user');
        } catch (AssignedItemException|IdentityException $t) {
            $io->error($t->getMessage());
            return $t->getCode() ?: ExitCode::UNSPECIFIED_ERROR;
        }

        return ExitCode::OK;
    }
}
