<?php

declare(strict_types=1);

namespace App\IdentityAccess\Presentation\Backend\Console\Access\Assign;

use App\IdentityAccess\Access\Application\Service\AccessManagementServiceInterface;
use App\IdentityAccess\Access\Application\Service\AccessRightsServiceInterface;
use App\IdentityAccess\Access\Application\Service\AssignAccessServiceInterface;
use App\IdentityAccess\Access\Application\Service\PermissionDTO;
use App\IdentityAccess\Access\Domain\Exception\AssignedItemException;
use App\IdentityAccess\Access\Domain\Exception\ExistItemException;
use App\IdentityAccess\Access\Domain\Exception\NotExistItemException;
use App\IdentityAccess\User\Application\Service\UserQueryServiceInterface;
use App\IdentityAccess\User\Domain\Exception\IdentityException;
use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Yiisoft\Yii\Console\ExitCode;

#[AsCommand(
    'assign:permission',
    'Assign RBAC permission to given user',
    help: 'This command allows you to assign RBAC permission to user',
)]
final class AssignPermissionCommand extends Command
{
    public function __construct(
        private readonly AssignAccessServiceInterface $assignAccessService,
        private readonly AccessManagementServiceInterface $accessManagementService,
        private readonly AccessRightsServiceInterface $accessRightsService,
        private readonly UserQueryServiceInterface $userQueryService,
    ) {
        parent::__construct();
    }

    #[Override]
    public function configure(): void
    {
        $this
            ->addArgument('userId', InputArgument::REQUIRED, 'User id')
            ->addArgument('permission', InputArgument::REQUIRED, 'RBAC permission');
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $permissionName = (string)$input->getArgument('permission');
        $userId = (string)$input->getArgument('userId');

        try {
            if (!$this->accessRightsService->existPermission($permissionName)) {
                /**
                 * @var QuestionHelper $helper
                 **/
                $helper = $this->getHelper('question');
                $question = new ConfirmationQuestion('Permission doesn\'t exist. Create new one? ', false);

                if (!$helper->ask($input, $output, $question)) {
                    return ExitCode::OK;
                }

                $permission = new PermissionDTO($permissionName);
                $this->accessManagementService->addPermission($permission);
            }

            $user = $this->userQueryService->getUser((int)$userId);
            if ($user === null || ($userId = $user->getId()) === null) {
                throw new IdentityException('User is not found!');
            }

            $permission = new PermissionDTO($permissionName);
            $this->assignAccessService->assignPermission($permission, $userId);

            $io->success('Permission was assigned to given user');
        } catch (ExistItemException|NotExistItemException|AssignedItemException|IdentityException $t) {
            $io->error($t->getMessage());
            return $t->getCode() ?: ExitCode::UNSPECIFIED_ERROR;
        }

        return ExitCode::OK;
    }
}
