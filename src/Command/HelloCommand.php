<?php

namespace MyCommands\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'hello',
    description: 'Displays a personalized greeting.',
)]
class HelloCommand extends Command
{
    protected function configure(): void
    {
        $this->setHelp('This command allows you to receive a greeting in the command line.');
        $this
            ->addArgument('name', InputArgument::OPTIONAL, 'Your name');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        if ($name) {
            $output->writeln(sprintf('Hello, %s! This is your Symfony Console command.', $name));
        } else {
            $output->writeln('Hello! This is your Symfony Console command.');
        }
        return Command::SUCCESS;
    }
}
