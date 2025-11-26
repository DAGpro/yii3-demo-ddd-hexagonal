<?php

declare(strict_types=1);

namespace App\IdentityAccess\Access\Slice\Controller\Backend\Console\Assign;

use App\IdentityAccess\Access\Domain\Exception\AssignedItemException;
use App\IdentityAccess\Access\Domain\Exception\NotExistItemException;
use App\IdentityAccess\Access\Slice\Service\AccessRightsServiceInterface;
use App\IdentityAccess\Access\Slice\Service\AssignAccessServiceInterface;
use App\IdentityAccess\Access\Slice\Service\AssignmentsServiceInterface;
use App\IdentityAccess\User\Domain\Exception\IdentityException;
use App\IdentityAccess\User\Slice\User\UserQueryServiceInterface;
use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Yiisoft\Yii\Console\ExitCode;

#[AsCommand(
    'assign:allPermissions',
    'Assign RBAC all permissions to given user',
    help: 'This command allows you to assign RBAC all permissions to user',
)]
final class AssignAllPermissionsCommand extends Command
{
    public function __construct(
        private readonly AssignAccessServiceInterface $assignAccessService,
        private readonly AccessRightsServiceInterface $accessRightsService,
        private readonly AssignmentsServiceInterface $assignmentsService,
        private readonly UserQueryServiceInterface $userService,
    ) {
        parent::__construct();
    }

    #[Override]
    public function configure(): void
    {
        $this->addArgument('userId', InputArgument::REQUIRED, 'User id');
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $userId = (string) $input->getArgument('userId');

        try {
            $user = $this->userService->getUser((int) $userId);
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
        } catch (AssignedItemException|IdentityException|NotExistItemException $t) {
            $io->error($t->getMessage());
            return $t->getCode() ?: ExitCode::UNSPECIFIED_ERROR;
        }

        return ExitCode::OK;
    }
}
