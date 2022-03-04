<?php

declare(strict_types=1);

namespace App\IdentityAccess\Presentation\Backend\Console\Access\Management;

use App\IdentityAccess\Access\Application\Service\AccessManagementServiceInterface;
use App\IdentityAccess\Access\Application\Service\PermissionDTO;
use App\IdentityAccess\Access\Application\Service\RoleDTO;
use App\IdentityAccess\Access\Domain\Exception\ExistItemException;
use App\IdentityAccess\Access\Domain\Exception\NotExistItemException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Yiisoft\Yii\Console\ExitCode;

final class AddChildPermissionCommand extends Command
{
    protected static $defaultName = 'access/addChildPermission';

    private AccessManagementServiceInterface $accessManagementService;

    public function __construct(AccessManagementServiceInterface $managementService)
    {
        $this->accessManagementService = $managementService;
        parent::__construct();
    }

    public function configure(): void
    {
        $this
            ->setDescription('Add child permission for a role in access control rights')
            ->setHelp('This command adds a child permission to a role in access control rights')
            ->addArgument('parentRole', InputArgument::REQUIRED, 'RBAC parent role')
            ->addArgument('childPermission', InputArgument::REQUIRED, 'RBAC child permission');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $parentRole = $input->getArgument('parentRole');
        $childPermission = $input->getArgument('childPermission');

        try {
            $parentRoleDTO = new RoleDTO($parentRole);
            $childPermissionDTO = new PermissionDTO($childPermission);
            $this->accessManagementService->addChildPermission($parentRoleDTO, $childPermissionDTO);

            $io->success(
                sprintf(
                    'Child permission `%s` added to parent role `%s`!',
                    $childPermissionDTO->getName(),
                    $parentRoleDTO->getName()
                )
            );
        } catch (NotExistItemException|ExistItemException $t) {
            $io->error($t->getMessage());
            return ExitCode::UNSPECIFIED_ERROR;
        }

        return ExitCode::OK;
    }
}
