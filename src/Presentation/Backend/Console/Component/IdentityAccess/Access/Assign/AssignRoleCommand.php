<?php

declare(strict_types=1);

namespace App\Presentation\Backend\Console\Component\IdentityAccess\Access\Assign;

use App\IdentityAccess\Access\Application\Service\AccessManagementServiceInterface;
use App\IdentityAccess\Access\Application\Service\AccessRightsServiceInterface;
use App\IdentityAccess\Access\Application\Service\AssignAccessServiceInterface;
use App\IdentityAccess\Access\Application\Service\RoleDTO;
use App\IdentityAccess\Access\Domain\Exception\AssignedItemException;
use App\IdentityAccess\Access\Domain\Exception\ExistItemException;
use App\IdentityAccess\Access\Domain\Exception\NotExistItemException;
use App\IdentityAccess\User\Application\Service\UserQueryServiceInterface;
use App\IdentityAccess\User\Domain\Exception\IdentityException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Yiisoft\Yii\Console\ExitCode;

final class AssignRoleCommand extends Command
{
    protected static $defaultName = 'assign/assignRole';

    private AssignAccessServiceInterface $assignAccessService;
    private AccessManagementServiceInterface $accessManagementService;
    private AccessRightsServiceInterface $accessRightsService;
    private UserQueryServiceInterface $userQueryService;

    public function __construct(
        AssignAccessServiceInterface $assignAccessService,
        AccessManagementServiceInterface $managementService,
        AccessRightsServiceInterface $accessRightsService,
        UserQueryServiceInterface $userQueryService
    ) {
        $this->assignAccessService = $assignAccessService;
        $this->accessManagementService = $managementService;
        $this->accessRightsService = $accessRightsService;
        $this->userQueryService = $userQueryService;
        parent::__construct();
    }

    public function configure(): void
    {
        $this
            ->setDescription('Assign RBAC role to given user')
            ->setHelp('This command allows you to assign RBAC role to user')
            ->addArgument('userId', InputArgument::REQUIRED, 'User id')
            ->addArgument('role', InputArgument::REQUIRED, 'RBAC role');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $roleName = $input->getArgument('role');
        $userId = $input->getArgument('userId');

        try {
            if (!$this->accessRightsService->existRole($roleName)) {
                $helper = $this->getHelper('question');
                $question = new ConfirmationQuestion('Role doesn\'t exist. Create new one? ', false);

                if (!$helper->ask($input, $output, $question)) {
                    return ExitCode::OK;
                }

                $role = new RoleDTO($roleName);
                $this->accessManagementService->addRole($role);
            }

            $user = $this->userQueryService->getUser((int)$userId);
            if ($user === null) {
                throw new IdentityException('This user was not found!');
            }

            $role = new RoleDTO($roleName);
            $this->assignAccessService->assignRole($role, $user->getId());

            $io->success('Role was assigned to given user');
        } catch (ExistItemException|NotExistItemException|AssignedItemException|IdentityException $t) {
            $io->error($t->getMessage());
            return $t->getCode() ?: ExitCode::UNSPECIFIED_ERROR;
        }

        return ExitCode::OK;
    }
}
