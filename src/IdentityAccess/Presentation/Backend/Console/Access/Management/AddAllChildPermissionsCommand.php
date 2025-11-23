<?php

declare(strict_types=1);

namespace App\IdentityAccess\Presentation\Backend\Console\Access\Management;

use App\IdentityAccess\Access\Application\Service\AccessManagementServiceInterface;
use App\IdentityAccess\Access\Application\Service\AccessRightsServiceInterface;
use App\IdentityAccess\Access\Application\Service\RoleDTO;
use App\IdentityAccess\Access\Domain\Exception\ExistItemException;
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
    'access:addAllChildPermissions',
    'Add all child permissions for a role in access control rights',
    help: 'This command adds a all child permissions to a role in access control rights',
)]
final class AddAllChildPermissionsCommand extends Command
{
    public function __construct(
        private readonly AccessRightsServiceInterface $accessRightsService,
        private readonly AccessManagementServiceInterface $accessManagementService,
    ) {
        parent::__construct();
    }

    #[Override]
    public function configure(): void
    {
        $this->addArgument('parentRole', InputArgument::REQUIRED, 'RBAC parent role');
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $parentRole = (string) $input->getArgument('parentRole');

        try {
            $parentRoleDTO = new RoleDTO($parentRole);

            $childPermissions = $this->accessRightsService->getPermissionsByRole($parentRoleDTO);
            $permissions = $this->accessRightsService->getPermissions();
            foreach ($permissions as $permission) {
                if (array_key_exists($permission->getName(), $childPermissions)) {
                    continue;
                }
                $this->accessManagementService->addChildPermission($parentRoleDTO, $permission);
            }

            $io->success(
                sprintf(
                    'All Child permissions added to parent role `%s`!',
                    $parentRoleDTO->getName(),
                ),
            );
        } catch (NotExistItemException|ExistItemException $t) {
            $io->error($t->getMessage());
            return $t->getCode() ?: ExitCode::UNSPECIFIED_ERROR;
        }

        return ExitCode::OK;
    }
}
