<?php

declare(strict_types=1);

namespace App\Presentation\Backend\Console\Component\IdentityAccess\User;

use App\IdentityAccess\Access\Application\Service\AccessRightsServiceInterface;
use App\IdentityAccess\Access\Application\Service\AssignAccessServiceInterface;
use App\IdentityAccess\User\Application\Service\UserQueryServiceInterface;
use App\IdentityAccess\User\Application\Service\UserServiceInterface;
use App\IdentityAccess\User\Domain\Exception\IdentityException;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;
use Yiisoft\Yii\Console\ExitCode;

final class CreateUserCommand extends Command
{
    private AssignAccessServiceInterface $assignAccessService;
    private AccessRightsServiceInterface $accessRightsService;
    private UserServiceInterface $userService;
    private UserQueryServiceInterface $userQueryService;

    protected static $defaultName = 'user/create';

    public function __construct(
        AssignAccessServiceInterface $assignAccessService,
        AccessRightsServiceInterface $rolesStorage,
        UserServiceInterface $userService,
        UserQueryServiceInterface $userQueryService
    ) {
        $this->assignAccessService = $assignAccessService;
        $this->accessRightsService = $rolesStorage;
        $this->userService = $userService;
        $this->userQueryService = $userQueryService;
        parent::__construct();
    }

    public function configure(): void
    {
        $this
            ->setDescription('Creates a user')
            ->setHelp('This command allows you to create a user')
            ->addArgument('login', InputArgument::REQUIRED, 'Login')
            ->addArgument('password', InputArgument::REQUIRED, 'Password')
            ->addArgument('isAdmin', InputArgument::OPTIONAL, 'Create user as admin');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $login = $input->getArgument('login');
        $password = $input->getArgument('password');
        $isAdmin = (bool)$input->getArgument('isAdmin');

        try {
            $this->userService->createUser($login, $password);

            if ($isAdmin) {

                $role = $this->accessRightsService->getRoleByName('admin');
                $user = $this->userQueryService->findByLogin($login);

                if ($user === null) {
                    throw new IdentityException('Failed to create user, please try again!');
                }

                if ($role === null) {
                    throw new Exception('Role admin is NULL');
                }

                $this->assignAccessService->assignRole($role, (string)$user->getId());
            }

            $io->success('User created');
        } catch (Throwable $t) {
            $io->error($t->getMessage());
            return $t->getCode() ?: ExitCode::UNSPECIFIED_ERROR;
        }
        return ExitCode::OK;
    }
}
