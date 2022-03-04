<?php

declare(strict_types=1);

namespace App\IdentityAccess\Presentation\Backend\Console\Access\Assign;

use App\IdentityAccess\Access\Application\Service\AssignAccessServiceInterface;
use App\IdentityAccess\Access\Application\Service\PermissionDTO;
use App\IdentityAccess\Access\Domain\Exception\AssignedItemException;
use App\IdentityAccess\User\Application\Service\UserQueryServiceInterface;
use App\IdentityAccess\User\Domain\Exception\IdentityException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Yiisoft\Yii\Console\ExitCode;

final class RevokePermissionCommand extends Command
{
    protected static $defaultName = 'assign/revokePermission';

    private AssignAccessServiceInterface $assigningService;
    private UserQueryServiceInterface $userQueryService;

    public function __construct(
        AssignAccessServiceInterface $assignAccessService,
        UserQueryServiceInterface $userQueryService
    ) {
        $this->assigningService = $assignAccessService;
        $this->userQueryService = $userQueryService;
        parent::__construct();
    }

    public function configure(): void
    {
        $this
            ->setDescription('Revoke RBAC permission to given user')
            ->setHelp('This command allows you to revoke RBAC permission to user')
            ->addArgument('userId', InputArgument::REQUIRED, 'User id')
            ->addArgument('permission', InputArgument::REQUIRED, 'RBAC permission');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $permissionName = $input->getArgument('permission');
        $userId = $input->getArgument('userId');

        try {
            $user = $this->userQueryService->getUser((int)$userId);
            if ($user === null) {
                throw new IdentityException('User is not found!');
            }

            $permissionDTO = new PermissionDTO($permissionName);
            $this->assigningService->revokePermission($permissionDTO, $user->getId());

            $io->success('Permission was revoke to given user');
        } catch (AssignedItemException|IdentityException $t) {
            $io->error($t->getMessage());
            return $t->getCode() ?: ExitCode::UNSPECIFIED_ERROR;
        }

        return ExitCode::OK;
    }
}
