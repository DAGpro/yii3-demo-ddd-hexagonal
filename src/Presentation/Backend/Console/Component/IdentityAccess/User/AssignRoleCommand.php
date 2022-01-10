<?php

declare(strict_types=1);

namespace App\Presentation\Backend\Console\Component\IdentityAccess\User;

use App\Core\Component\IdentityAccess\User\Application\Service\UserQueryServiceInterface;
use App\Core\Component\IdentityAccess\User\Domain\Exception\IdentityException;
use App\Core\Component\IdentityAccess\User\Domain\User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Yiisoft\Rbac\Manager;
use Yiisoft\Rbac\Role;
use Yiisoft\Rbac\RolesStorageInterface;
use Yiisoft\Yii\Console\ExitCode;

final class AssignRoleCommand extends Command
{
    private Manager $manager;
    private RolesStorageInterface $rolesStorage;
    private UserQueryServiceInterface $userQueryService;

    protected static $defaultName = 'user/assignRole';

    public function __construct(UserQueryServiceInterface $userQueryService, Manager $manager, RolesStorageInterface $rolesStorage)
    {
        $this->userQueryService = $userQueryService;
        $this->manager = $manager;
        $this->rolesStorage = $rolesStorage;
        parent::__construct();
    }

    public function configure(): void
    {
        $this
            ->setDescription('Assign RBAC role to given user')
            ->setHelp('This command allows you to assign RBAC role to user')
            ->addArgument('role', InputArgument::REQUIRED, 'RBAC role')
            ->addArgument('userId', InputArgument::REQUIRED, 'User id');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $roleName = $input->getArgument('role');
        $userId = (int)$input->getArgument('userId');

        try {
            /** @var User|null $user */
            $user = $this->userQueryService->getUser($userId);
            if ($user === null) {
                throw new IdentityException('This user was not found!');
            }

            $role = $this->rolesStorage->getRoleByName($roleName);

            if (null === $role) {
                $helper = $this->getHelper('question');
                $question = new ConfirmationQuestion('Role doesn\'t exist. Create new one? ', false);

                if (!$helper->ask($input, $output, $question)) {
                    return ExitCode::OK;
                }

                $role = new Role($roleName);
                $this->manager->addRole($role);
            }

            $this->manager->assign($role, (string)$user->getId());

            $io->success('Role was assigned to given user');
        } catch (IdentityException $t) {
            $io->error($t->getMessage());
            return $t->getCode() ?: ExitCode::UNSPECIFIED_ERROR;
        }

        return ExitCode::OK;
    }
}
