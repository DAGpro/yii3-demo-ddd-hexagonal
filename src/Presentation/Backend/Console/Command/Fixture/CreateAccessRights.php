<?php

namespace App\Presentation\Backend\Console\Command\Fixture;

use App\IdentityAccess\Access\Application\Service\AccessManagementServiceInterface;
use App\IdentityAccess\Access\Application\Service\AccessRightsServiceInterface;
use App\IdentityAccess\Access\Application\Service\PermissionDTO;
use App\IdentityAccess\Access\Application\Service\RoleDTO;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Yiisoft\Yii\Console\ExitCode;

final class CreateAccessRights extends Command
{
    protected static $defaultName = 'fixture/addAccess';

    private AccessManagementServiceInterface $accessManagementService;
    private AccessRightsServiceInterface $accessRightsService;

    public function __construct(
        AccessManagementServiceInterface $accessManagementService,
        AccessRightsServiceInterface $accessRightsService
    ) {
        $this->accessManagementService = $accessManagementService;
        $this->accessRightsService = $accessRightsService;
        parent::__construct();
    }

    public function configure(): void
    {
        $this
            ->setDescription('Add demo access rights')
            ->setHelp('This command adds demo access rights');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            if ($this->accessRightsService->existRole('admin')
                && $this->accessRightsService->existRole('author')
            ) {
                $io->error('Demo access rights have already been added!');
                return ExitCode::UNSPECIFIED_ERROR;
            }

            $this->accessManagementService->addRole(new RoleDTO('admin'));
            $this->accessManagementService->addRole(new RoleDTO('author'));

            $adminPermissions = [
                'indexDashboard',

                'indexPost',
                'viewPost',
                'draftPost',
                'publicPost',
                'moderatePost',
                'deletePost',

                'indexTag',
                'changeTag',
                'deleteTag',

                'indexComment',
                'viewComment',
                'draftComment',
                'publicComment',
                'moderateComment',
                'deleteComment',
            ];

            $authorPermissions = [
                'authorViewPost',
                'authorAddPost',
                'authorEditPost',
                'authorDeletePost',
                'authorPostsList',
            ];

            foreach ($adminPermissions as $permission) {
                $this->accessManagementService->addPermission(new PermissionDTO($permission));
            }

            foreach ($authorPermissions as $permission) {
                $this->accessManagementService->addPermission(new PermissionDTO($permission));
            }

            foreach ($adminPermissions as $permission) {
                $this->accessManagementService->addChildPermission(new RoleDTO('admin'), new PermissionDTO($permission));
            }

            foreach ($authorPermissions as $permission) {
                $this->accessManagementService->addChildPermission(new RoleDTO('author'), new PermissionDTO($permission));
            }
        } catch (\Throwable $t) {
            $io->error($t->getMessage());
            return $t->getCode() ?: ExitCode::UNSPECIFIED_ERROR;
        }
        $io->success('Done');
        return ExitCode::OK;
    }

}
