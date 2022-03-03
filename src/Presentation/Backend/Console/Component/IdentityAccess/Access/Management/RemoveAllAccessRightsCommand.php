<?php

declare(strict_types=1);

namespace App\Presentation\Backend\Console\Component\IdentityAccess\Access\Management;

use App\IdentityAccess\Access\Application\Service\AccessManagementServiceInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Yiisoft\Yii\Console\ExitCode;

final class RemoveAllAccessRightsCommand extends Command
{
    protected static $defaultName = 'access/removeAll';

    private AccessManagementServiceInterface $accessManagementService;

    public function __construct(AccessManagementServiceInterface $managementService)
    {
        $this->accessManagementService = $managementService;
        parent::__construct();
    }

    public function configure(): void
    {
        $this
            ->setDescription('Remove all access control rights!')
            ->setHelp('This command allows you to remove all access control rights');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->accessManagementService->clearAccessRights();

            $io->success('Removed all access rights!');
        } catch (\Throwable $t) {
            $io->error($t->getMessage());
            return $t->getCode() ?: ExitCode::UNSPECIFIED_ERROR;
        }

        return ExitCode::OK;
    }
}
