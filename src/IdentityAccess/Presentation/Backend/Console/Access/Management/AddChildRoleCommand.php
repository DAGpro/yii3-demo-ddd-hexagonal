<?php

declare(strict_types=1);

namespace App\IdentityAccess\Presentation\Backend\Console\Access\Management;

use App\IdentityAccess\Access\Application\Service\AccessManagementServiceInterface;
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
    'access:addChildRole',
    'Add child role for a role in access control rights',
    help: 'This command adds a child role to a role in access control rights'
)]
final class AddChildRoleCommand extends Command
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
            ->addArgument('childRole', InputArgument::REQUIRED, 'RBAC child role');
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $parentRole = (string)$input->getArgument('parentRole');
        $childRole = (string)$input->getArgument('childRole');

        try {
            $parentRoleDTO = new RoleDTO($parentRole);
            $childRoleDTO = new RoleDTO($childRole);

            $this->accessManagementService->addChildRole($parentRoleDTO, $childRoleDTO);

            $io->success(
                sprintf(
                    'Child role `%s` added to parent role `%s`!',
                    $childRoleDTO->getName(),
                    $parentRoleDTO->getName(),
                ),
            );

            return ExitCode::OK;
        } catch (NotExistItemException|ExistItemException $t) {
            $io->error($t->getMessage());
            return $t->getCode() ?: ExitCode::UNSPECIFIED_ERROR;
        }
    }
}
