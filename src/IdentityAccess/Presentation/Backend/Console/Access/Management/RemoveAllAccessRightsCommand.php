<?php

declare(strict_types=1);

namespace App\IdentityAccess\Presentation\Backend\Console\Access\Management;

use App\IdentityAccess\Access\Application\Service\AccessManagementServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;
use Yiisoft\Yii\Console\ExitCode;

#[AsCommand(
    'access:removeAll',
    'Remove all access control rights!'
)]
final class RemoveAllAccessRightsCommand extends Command
{
    public function __construct(
        private readonly AccessManagementServiceInterface $accessManagementService,
    ) {
        parent::__construct();
    }

    #[\Override]
    public function configure(): void
    {
        $this->setHelp('This command allows you to remove all access control rights');
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->accessManagementService->clearAccessRights();

            $io->success('Removed all access rights!');
        } catch (Throwable $t) {
            $io->error($t->getMessage());
            return $t->getCode() ?: ExitCode::UNSPECIFIED_ERROR;
        }

        return ExitCode::OK;
    }
}
