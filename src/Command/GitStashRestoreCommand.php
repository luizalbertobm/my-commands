<?php

namespace MyCommands\Command;

use MyCommands\Helper\GitHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'git:stash-restore',
    description: 'Apply a specific stash after showing the last 3 stashes.'
)]
class GitStashRestoreCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Get the stashes
        $stashes = GitHelper::listStashes();
        if (empty($stashes)) {
            $io->warning('No stashes found.');
            return Command::SUCCESS;
        }

        // Display the last 5 stashes
        $io->section('Last 5 Stashes');
        $lastStashes = array_slice($stashes, 0, 5); // Limit to the first 5 items
        foreach ($lastStashes as $index => $stash) {
            $io->writeln(sprintf('[%d] %s', $index, $stash));
        }
        $io->writeln('Note: The stash list may be truncated for display purposes.');
        $io->writeln('You can use the stash index to apply a specific stash.');

        // Ask the user to select a stash
        $selectedIndex = $io->ask('Enter the number of the stash to apply', null, function ($value) use ($stashes) {
            if (!is_numeric($value) || !isset($stashes[(int)$value])) {
                throw new \RuntimeException('Invalid selection. Please enter a valid stash number.');
            }
            return (int)$value;
        });
        if (!isset($stashes[$selectedIndex])) {
            $io->error('Invalid stash selection.');
            return Command::FAILURE;
        }

        // Apply the selected stash
        $selectedStash = $stashes[$selectedIndex];
        try {
            GitHelper::applyStash((int)$selectedStash);
        } catch (\Exception $e) {
            $io->error('Failed to apply stash: ' . $e->getMessage());
            return Command::FAILURE;
        }

        $io->success(sprintf('Successfully applied stash: %s', $selectedStash));

        return Command::SUCCESS;
    }
}
