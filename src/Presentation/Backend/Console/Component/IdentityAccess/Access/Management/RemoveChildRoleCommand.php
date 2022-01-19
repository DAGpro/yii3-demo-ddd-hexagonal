<?php

declare(strict_types=1);

namespace App\Presentation\Backend\Console\Component\IdentityAccess\Access\Management;

use App\Core\Component\IdentityAccess\Access\Application\Service\AccessManagementServiceInterface;
use App\Core\Component\IdentityAccess\Access\Application\Service\RoleDTO;
use App\Core\Component\IdentityAccess\Access\Domain\Exception\NotExistItemException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Yiisoft\Yii\Console\ExitCode;

class RemoveChildRoleCommand extends Command
{
    protected static $defaultName = 'access/removeChildRole';

    private AccessManagementServiceInterface $accessManagementService;

    public function __construct(AccessManagementServiceInterface $managementService)
    {
        $this->accessManagementService = $managementService;
        parent::__construct();
    }

    public function configure(): void
    {
        $this
            ->setDescription('Remove child role for a role in access control rights')
            ->setHelp('This command remove a child role to a role in access control rights')
            ->addArgument('parentRole', InputArgument::REQUIRED, 'RBAC parent role')
            ->addArgument('childRole', InputArgument::REQUIRED, 'RBAC child role');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $parentRole = $input->getArgument('parentRole');
        $childRole = $input->getArgument('childRole');

        try {
            $parentRoleDTO = new RoleDTO($parentRole);
            $childRoleDTO = new RoleDTO($childRole);
            $this->accessManagementService->removeChildRole($parentRoleDTO, $childRoleDTO);

            $io->success(
                sprintf(
                    'Child role `%s` remove to parent role `%s`!',
                    $childRoleDTO->getName(),
                    $parentRoleDTO->getName()
                )
            );
        } catch (NotExistItemException $t) {
            $io->error($t->getMessage());
            return $t->getCode() ?: ExitCode::UNSPECIFIED_ERROR;
        }

        return ExitCode::OK;
    }
}
