<?php

declare(strict_types=1);

namespace App\Site\Presentation\Backend\Console\Translation;

use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Yiisoft\Translator\TranslatorInterface;

#[AsCommand(name: 'translator:translate', description: 'Translates a message')]
final class TranslateCommand extends Command
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        $this->addArgument('message', InputArgument::REQUIRED, 'Message that will be translated.');
        $this->addArgument('locale', InputArgument::OPTIONAL, 'Translation locale.');
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $message = $input->getArgument('message');
        $locale = $input->getArgument('locale');

        $output->writeln($this->translator->translate($message, [], null, $locale));
        return 0;
    }
}
