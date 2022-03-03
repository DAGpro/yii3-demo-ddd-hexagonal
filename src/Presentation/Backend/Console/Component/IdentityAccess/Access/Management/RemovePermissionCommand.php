<?php

declare(strict_types=1);

namespace App\Presentation\Backend\Console\Component\IdentityAccess\Access\Management;

use App\IdentityAccess\Access\Application\Service\AccessManagementServiceInterface;
use App\IdentityAccess\Access\Application\Service\PermissionDTO;
use App\IdentityAccess\Access\Domain\Exception\AssignedItemException;
use App\IdentityAccess\Access\Domain\Exception\NotExistItemException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Yiisoft\Yii\Console\ExitCode;

final class RemovePermissionCommand extends Command
{
    protected static $defaultName = 'access/removePermission';

    private AccessManagementServiceInterface $accessManagementService;

    public function __construct(AccessManagementServiceInterface $managementService)
    {
        $this->accessManagementService = $managementService;
        parent::__construct();
    }

    public function configure(): void
    {
        $this
            ->setDescription('Remove permission from access control rights')
            ->setHelp('This command allows you to remove permission from access control rights')
            ->addArgument('permission', InputArgument::REQUIRED, 'RBAC permission');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $permissionName = $input->getArgument('permission');

        try {
            $permissionDTO = new PermissionDTO($permissionName);
            $this->accessManagementService->removePermission($permissionDTO);

            $io->success(
                sprintf(
                    '`%s` access permission has been removed!',
                    $permissionDTO->getName(),
                )
            );
        } catch (NotExistItemException|AssignedItemException $t) {
            $io->error($t->getMessage());
            return $t->getCode() ?: ExitCode::UNSPECIFIED_ERROR;
        }

        return ExitCode::OK;
    }
}
