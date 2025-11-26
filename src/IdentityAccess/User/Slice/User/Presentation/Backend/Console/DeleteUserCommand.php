<?php

declare(strict_types=1);

namespace App\IdentityAccess\User\Slice\User\Presentation\Backend\Console;

use App\IdentityAccess\User\Domain\Exception\IdentityException;
use App\IdentityAccess\User\Slice\User\UserQueryServiceInterface;
use App\IdentityAccess\User\Slice\User\UserServiceInterface;
use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Yiisoft\Yii\Console\ExitCode;

#[AsCommand(
    'user:delete',
    'Deletes a user',
    help: 'This command allows you to delete a user',
)]
final class DeleteUserCommand extends Command
{
    public function __construct(
        private readonly UserServiceInterface $userService,
        private readonly UserQueryServiceInterface $userQueryService,
    ) {
        parent::__construct();
    }

    #[Override]
    public function configure(): void
    {
        $this
            ->addArgument('login', InputArgument::REQUIRED, 'Login');
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $login = (string) $input->getArgument('login');

        try {
            $user = $this->userQueryService->findByLogin($login);
            if ($user === null || ($userId = $user->getId()) === null) {
                throw new IdentityException('User is not found!');
            }
            $this->userService->deleteUser($userId);

            $io->success('User deleted');
        } catch (IdentityException $t) {
            $io->error($t->getMessage());
            return $t->getCode() ?: ExitCode::UNSPECIFIED_ERROR;
        }
        return ExitCode::OK;
    }
}
