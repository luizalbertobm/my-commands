<?php

namespace MyCommands\Command;

use MyCommands\Helper\GitHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'git:stash',
    description: 'Stash the current changes with an optional message.'
)]
class GitStashCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Git Stash Command');

        // Ask for a stash message
        $comment = $io->ask('Enter a message for the stash (leave empty for default message)');

        try {
            GitHelper::stashChanges($comment);
            $io->success('Changes have been successfully stashed.');
        } catch (\Exception $e) {
            $io->error('Failed to stash changes: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
