<?php

declare(strict_types=1);

namespace App\Presentation\Backend\Console\Component\IdentityAccess\Access\Assign;

use App\Core\Component\IdentityAccess\Access\Application\Service\AccessRightsServiceInterface;
use App\Core\Component\IdentityAccess\Access\Application\Service\AssignAccessServiceInterface;
use App\Core\Component\IdentityAccess\Access\Application\Service\AssignmentsServiceInterface;
use App\Core\Component\IdentityAccess\Access\Domain\Exception\AssignedItemException;
use App\Core\Component\IdentityAccess\User\Application\Service\UserQueryServiceInterface;
use App\Core\Component\IdentityAccess\User\Domain\Exception\IdentityException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Yiisoft\Yii\Console\ExitCode;

class AssignAllPermissionsCommand extends Command
{
    protected static $defaultName = 'assign/assignAllPermissions';

    private AssignAccessServiceInterface $assignAccessService;
    private AccessRightsServiceInterface $accessRightsService;
    private UserQueryServiceInterface $userService;
    private AssignmentsServiceInterface $assignmentsService;

    public function __construct(
        AssignAccessServiceInterface $assignAccessService,
        AccessRightsServiceInterface $accessRightsService,
        AssignmentsServiceInterface $assignmentsService,
        UserQueryServiceInterface $userService
    ) {
        $this->assignAccessService = $assignAccessService;
        $this->accessRightsService = $accessRightsService;
        $this->userService = $userService;
        $this->assignmentsService = $assignmentsService;
        parent::__construct();
    }

    public function configure(): void
    {
        $this
            ->setDescription('Assign RBAC all permissions to given user')
            ->setHelp('This command allows you to assign RBAC all permissions to user')
            ->addArgument('userId', InputArgument::REQUIRED, 'User id');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $userId = $input->getArgument('userId');

        try {
            $user = $this->userService->getUser((int)$userId);
            if ($user === null) {
                throw new IdentityException('User is not found!');
            }

            $userPermissions = $this->assignmentsService->getPermissionsByUser($userId);
            $permissions = $this->accessRightsService->getPermissions();
            foreach ($permissions as $permission) {
                if (array_key_exists($permission->getName(), $userPermissions)) {
                    continue;
                }

                $this->assignAccessService->assignPermission($permission, $userId);
            }

            $io->success('All Permissions was assigned to given user');
        } catch (AssignedItemException|IdentityException $t) {
            $io->error($t->getMessage());
            return $t->getCode() ?: ExitCode::UNSPECIFIED_ERROR;
        }

        return ExitCode::OK;
    }
}
