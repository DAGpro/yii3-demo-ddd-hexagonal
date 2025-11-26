<?php

declare(strict_types=1);

namespace App\IdentityAccess\User\Slice\User\Presentation\Backend\Console;

use App\IdentityAccess\Access\Slice\Service\AccessRightsServiceInterface;
use App\IdentityAccess\Access\Slice\Service\AssignAccessServiceInterface;
use App\IdentityAccess\User\Domain\Exception\IdentityException;
use App\IdentityAccess\User\Slice\User\UserQueryServiceInterface;
use App\IdentityAccess\User\Slice\User\UserServiceInterface;
use Override;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;
use Yiisoft\Validator\Rule\Length;
use Yiisoft\Validator\Rule\Required;
use Yiisoft\Validator\Validator;
use Yiisoft\Yii\Console\ExitCode;

#[AsCommand(
    'user:create',
    'Creates a user [login] [password] [isAdmin:]',
    help: 'This command allows you to create a user',
)]
final class CreateUserCommand extends Command
{
    public function __construct(
        private readonly AssignAccessServiceInterface $assignAccessService,
        private readonly AccessRightsServiceInterface $accessRightsService,
        private readonly UserServiceInterface $userService,
        private readonly UserQueryServiceInterface $userQueryService,
        private readonly Validator $validator,
    ) {
        parent::__construct();
    }

    #[Override]
    public function configure(): void
    {
        $this
            ->addArgument('login', InputArgument::REQUIRED, 'Login')
            ->addArgument('password', InputArgument::REQUIRED, 'Password')
            ->addArgument('isAdmin', InputArgument::OPTIONAL, 'Create user as admin');
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $login = (string) $input->getArgument('login');
        $password = (string) $input->getArgument('password');
        $isAdmin = (bool) $input->getArgument('isAdmin');

        try {
            $result = $this->validator->validate(
                ['login' => $login, 'password' => $password],
                [
                    'login' => [
                        new Required(),
                        new Length(min: 3),
                    ],
                    'password' => [
                        new Required(),
                        new Length(min: 3),
                    ],
                ],
            );

            if (!$result->isValid()) {
                $io->error(implode(', ', $result->getErrorMessages()));
                return ExitCode::DATAERR;
            }

            $this->userService->createUser($login, $password);

            if ($isAdmin) {
                $role = $this->accessRightsService->getRoleByName('admin');
                $user = $this->userQueryService->findByLogin($login);

                if ($user === null) {
                    throw new IdentityException('Failed to create user, please try again!');
                }

                if ($role === null) {
                    throw new RuntimeException('Role admin is NULL');
                }

                $this->assignAccessService->assignRole($role, (string) $user->getId());
            }

            $io->success('User created');
        } catch (Throwable $t) {
            $io->error($t->getMessage());
            return (int) $t->getCode() ?: ExitCode::UNSPECIFIED_ERROR;
        }
        return ExitCode::OK;
    }
}
