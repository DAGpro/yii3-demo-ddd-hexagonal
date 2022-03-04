<?php

declare(strict_types=1);

namespace App\IdentityAccess\Presentation\Backend\Console\User;

use App\IdentityAccess\User\Application\Service\UserQueryServiceInterface;
use App\IdentityAccess\User\Application\Service\UserServiceInterface;
use App\IdentityAccess\User\Domain\Exception\IdentityException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Yiisoft\Yii\Console\ExitCode;

final class DeleteUserCommand extends Command
{
    protected static $defaultName = 'user/delete';

    private UserServiceInterface $userService;
    private UserQueryServiceInterface $userQueryService;

    public function __construct(
        UserServiceInterface $userService,
        UserQueryServiceInterface $userQueryService
    ) {
        $this->userService = $userService;
        $this->userQueryService = $userQueryService;
        parent::__construct();
    }

    public function configure(): void
    {
        $this
            ->setDescription('Deletes a user')
            ->setHelp('This command allows you to delete a user')
            ->addArgument('login', InputArgument::REQUIRED, 'Login');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $login = $input->getArgument('login');

        try {
            $user = $this->userQueryService->findByLogin($login);
            if ($user === null) {
                throw new IdentityException('User is not found!');
            }
            $this->userService->deleteUser($user->getId());

            $io->success('User deleted');
        } catch (IdentityException $t) {
            $io->error($t->getMessage());
            return $t->getCode() ?: ExitCode::UNSPECIFIED_ERROR;
        }
        return ExitCode::OK;
    }
}
