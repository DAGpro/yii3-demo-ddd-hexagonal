<?php

declare(strict_types=1);

namespace App\Presentation\Backend\Console\Component\IdentityAccess\Access\Assign;

use App\Core\Component\IdentityAccess\Access\Application\Service\AccessManagementServiceInterface;
use App\Core\Component\IdentityAccess\Access\Application\Service\AccessRightsServiceInterface;
use App\Core\Component\IdentityAccess\Access\Application\Service\AssignAccessServiceInterface;
use App\Core\Component\IdentityAccess\Access\Application\Service\PermissionDTO;
use App\Core\Component\IdentityAccess\Access\Domain\Exception\AssignedItemException;
use App\Core\Component\IdentityAccess\Access\Domain\Exception\ExistItemException;
use App\Core\Component\IdentityAccess\Access\Domain\Exception\NotExistItemException;
use App\Core\Component\IdentityAccess\User\Application\Service\UserQueryServiceInterface;
use App\Core\Component\IdentityAccess\User\Domain\Exception\IdentityException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Yiisoft\Yii\Console\ExitCode;

class AssignPermissionCommand extends Command
{
    protected static $defaultName = 'assign/assignPermission';

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
            ->setDescription('Assign RBAC permission to given user')
            ->setHelp('This command allows you to assign RBAC permission to user')
            ->addArgument('userId', InputArgument::REQUIRED, 'User id')
            ->addArgument('permission', InputArgument::REQUIRED, 'RBAC permission');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $permissionName = $input->getArgument('permission');
        $userId = $input->getArgument('userId');

        try {
            if (!$this->accessRightsService->existPermission($permissionName)) {
                $helper = $this->getHelper('question');
                $question = new ConfirmationQuestion('Permission doesn\'t exist. Create new one? ', false);

                if (!$helper->ask($input, $output, $question)) {
                    return ExitCode::OK;
                }

                $permission = new PermissionDTO($permissionName);
                $this->accessManagementService->addPermission($permission);
            }

            $user = $this->userQueryService->getUser((int)$userId);
            if ($user === null) {
                throw new IdentityException('User is not found!');
            }

            $permission = new PermissionDTO($permissionName);
            $this->assignAccessService->assignPermission($permission, $user->getId());

            $io->success('Permission was assigned to given user');
        } catch (ExistItemException|NotExistItemException|AssignedItemException|IdentityException $t) {
            $io->error($t->getMessage());
            return $t->getCode() ?: ExitCode::UNSPECIFIED_ERROR;
        }

        return ExitCode::OK;
    }
}
