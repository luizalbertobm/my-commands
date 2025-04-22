<?php
namespace MyCommands\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


// $progressBar = new ProgressBar($output, 50);
// https://symfony.com/doc/current/components/console/helpers/progressindicator.html
// https://symfony.com/doc/current/components/console/helpers/questionhelper.html

#[AsCommand(
    name: 'hello',
    description: 'Exibe uma saudação personalizada.',
)]
class HelloCommand extends Command
{

    protected function configure(): void
    {
        $this->setHelp('Este comando permite que você receba uma saudação na linha de comando.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Olá, Luiz Alberto! Este é seu comando Symfony Console.');
        return Command::SUCCESS;
    }
}
