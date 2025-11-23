<?php

declare(strict_types=1);

namespace App\IdentityAccess\Presentation\Backend\Console\Access\Management;

use App\IdentityAccess\Access\Application\Service\AccessManagementServiceInterface;
use App\IdentityAccess\Access\Application\Service\PermissionDTO;
use App\IdentityAccess\Access\Domain\Exception\AssignedItemException;
use App\IdentityAccess\Access\Domain\Exception\NotExistItemException;
use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Yiisoft\Yii\Console\ExitCode;

#[AsCommand(
    'access:removePermission',
    'Remove permission from access control rights',
    help: 'This command allows you to remove permission from access control rights',
)]
final class RemovePermissionCommand extends Command
{
    public function __construct(
        private readonly AccessManagementServiceInterface $accessManagementService,
    ) {
        parent::__construct();
    }

    #[Override]
    public function configure(): void
    {
        $this->addArgument('permission', InputArgument::REQUIRED, 'RBAC permission');
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $permissionName = (string) $input->getArgument('permission');

        try {
            $permissionDTO = new PermissionDTO($permissionName);
            $this->accessManagementService->removePermission($permissionDTO);

            $io->success(
                sprintf(
                    '`%s` access permission has been removed!',
                    $permissionDTO->getName(),
                ),
            );
        } catch (NotExistItemException|AssignedItemException $t) {
            $io->error($t->getMessage());
            return $t->getCode() ?: ExitCode::UNSPECIFIED_ERROR;
        }

        return ExitCode::OK;
    }
}
