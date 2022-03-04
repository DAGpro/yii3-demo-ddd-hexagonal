<?php

declare(strict_types=1);

namespace App\IdentityAccess\Presentation\Backend\Console\Access\Management;

use App\IdentityAccess\Access\Application\Service\AccessManagementServiceInterface;
use App\IdentityAccess\Access\Application\Service\AccessRightsServiceInterface;
use App\IdentityAccess\Access\Application\Service\RoleDTO;
use App\IdentityAccess\Access\Domain\Exception\ExistItemException;
use App\IdentityAccess\Access\Domain\Exception\NotExistItemException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Yiisoft\Yii\Console\ExitCode;

final class AddAllChildPermissionsCommand extends Command
{
    protected static $defaultName = 'access/addAllChildPermissions';

    private AccessManagementServiceInterface $accessManagementService;
    private AccessRightsServiceInterface $accessRightsService;

    public function __construct(
        AccessRightsServiceInterface $accessRightsService,
        AccessManagementServiceInterface $managementService
    ) {
        $this->accessManagementService = $managementService;
        parent::__construct();
        $this->accessRightsService = $accessRightsService;
    }

    public function configure(): void
    {
        $this
            ->setDescription('Add all child permissions for a role in access control rights')
            ->setHelp('This command adds a all child permissions to a role in access control rights')
            ->addArgument('parentRole', InputArgument::REQUIRED, 'RBAC parent role');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $parentRole = $input->getArgument('parentRole');

        try {
            $parentRoleDTO = new RoleDTO($parentRole);

            $childPermission = $this->accessRightsService->getPermissionsByRole($parentRoleDTO);
            $permissions = $this->accessRightsService->getPermissions();
            foreach ($permissions as $permission) {
                if (array_key_exists($permission->getName, $childPermission)) {
                    continue;
                }
                $this->accessManagementService->addChildPermission($parentRoleDTO, $permission);
            }

            $io->success(
                sprintf(
                    'All Child permissions added to parent role `%s`!',
                    $parentRoleDTO->getName()
                )
            );
        } catch (NotExistItemException|ExistItemException $t) {
            $io->error($t->getMessage());
            return $t->getCode() ?: ExitCode::UNSPECIFIED_ERROR;
        }

        return ExitCode::OK;
    }
}
