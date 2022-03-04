<?php

declare(strict_types=1);

namespace App\IdentityAccess\Presentation\Backend\Console\Access\Management;

use App\IdentityAccess\Access\Application\Service\AccessManagementServiceInterface;
use App\IdentityAccess\Access\Application\Service\RoleDTO;
use App\IdentityAccess\Access\Domain\Exception\ExistItemException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Yiisoft\Yii\Console\ExitCode;

final class AddRoleCommand extends Command
{
    protected static $defaultName = 'access/addRole';

    private AccessManagementServiceInterface $accessManagementService;

    public function __construct(AccessManagementServiceInterface $managementService)
    {
        $this->accessManagementService = $managementService;
        parent::__construct();
    }

    public function configure(): void
    {
        $this
            ->setDescription('Add role from access control rights')
            ->setHelp('This command allows you to add role from access control rights')
            ->addArgument('role', InputArgument::REQUIRED, 'RBAC role');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $roleName = $input->getArgument('role');

        try {
            $roleDTO = new RoleDTO($roleName);
            $this->accessManagementService->addRole($roleDTO);

            $io->success(
                sprintf(
                    '`%s` access role has been created!!',
                    $roleDTO->getName(),
                )
            );
        } catch (ExistItemException $t) {
            $io->error($t->getMessage());
            return $t->getCode() ?: ExitCode::UNSPECIFIED_ERROR;
        }

        return ExitCode::OK;
    }
}
