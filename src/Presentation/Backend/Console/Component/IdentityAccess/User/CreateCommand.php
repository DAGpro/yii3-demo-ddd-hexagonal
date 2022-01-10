<?php

declare(strict_types=1);

namespace App\Presentation\Backend\Console\Component\IdentityAccess\User;

use App\Core\Component\IdentityAccess\User\Application\Service\UserQueryServiceInterface;
use App\Core\Component\IdentityAccess\User\Domain\Exception\IdentityException;
use App\Infrastructure\Authentication\SignupUserService;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;
use Yiisoft\Rbac\Manager;
use Yiisoft\Rbac\RolesStorageInterface;
use Yiisoft\Yii\Console\ExitCode;

final class CreateCommand extends Command
{
    private Manager $manager;
    private RolesStorageInterface $rolesStorage;
    private SignupUserService $signupUserService;
    private UserQueryServiceInterface $userQueryService;

    protected static $defaultName = 'user/create';

    public function __construct(
        Manager $manager,
        RolesStorageInterface $rolesStorage,
        SignupUserService $signupUserService,
        UserQueryServiceInterface $userQueryService
    ) {
        $this->manager = $manager;
        $this->rolesStorage = $rolesStorage;
        $this->signupUserService = $signupUserService;
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
            $this->signupUserService->signup($login, $password);

            if ($isAdmin) {

                $role = $this->rolesStorage->getRoleByName('admin');
                $user = $this->userQueryService->findByLogin($login);

                if ($user === null) {
                    throw new IdentityException('Failed to create user, please try again!');
                }

                if ($role === null) {
                    throw new Exception('Role admin is NULL');
                }

                $this->manager->assign($role, (string)$user->getId());
            }

            $io->success('User created');
        } catch (Throwable $t) {
            $io->error($t->getMessage());
            return $t->getCode() ?: ExitCode::UNSPECIFIED_ERROR;
        }
        return ExitCode::OK;
    }
}
