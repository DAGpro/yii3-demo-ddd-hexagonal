<?php

declare(strict_types=1);

namespace App\IdentityAccess\Presentation\Backend\Console\Access\Management;

use App\IdentityAccess\Access\Application\Service\AccessManagementServiceInterface;
use App\IdentityAccess\Access\Application\Service\PermissionDTO;
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
    'access:addChildPermission',
    'Add child permission for a role in access control rights',
    help: 'This command add a child permission to a role in access control rights',
)]
final class AddChildPermissionCommand extends Command
{
    public function __construct(
        private readonly AccessManagementServiceInterface $accessManagementService,
    ) {
        parent::__construct();
    }

    #[Override]
    public function configure(): void
    {
        $this
            ->addArgument('parentRole', InputArgument::REQUIRED, 'RBAC parent role')
            ->addArgument('childPermission', InputArgument::REQUIRED, 'RBAC child permission');
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $parentRole = (string) $input->getArgument('parentRole');
        $childPermission = (string) $input->getArgument('childPermission');

        try {
            $parentRoleDTO = new RoleDTO($parentRole);
            $childPermissionDTO = new PermissionDTO($childPermission);
            $this->accessManagementService->addChildPermission($parentRoleDTO, $childPermissionDTO);

            $io->success(
                sprintf(
                    'Child permission `%s` added to parent role `%s`!',
                    $childPermissionDTO->getName(),
                    $parentRoleDTO->getName(),
                ),
            );
        } catch (NotExistItemException|ExistItemException $t) {
            $io->error($t->getMessage());
            return ExitCode::UNSPECIFIED_ERROR;
        }

        return ExitCode::OK;
    }
}
