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
    description: 'Apply a specific stash after showing the last 5 stashes.'
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

        // Prepare stash options for choice menu (limited to 5)
        $lastStashes = array_slice($stashes, 0, 5);
        $stashOptions = [];
        foreach ($lastStashes as $index => $stash) {
            $stashOptions[$index] = sprintf('%s', $stash);
        }
        
        if (count($stashes) > 5) {
            $io->note('Showing only the last 5 stashes. There are ' . count($stashes) . ' stashes in total.');
        }

        // Use the choice method to let the user select a stash
        $selectedOption = $io->choice('Select a stash to apply', $stashOptions);
        
        // Extract the index from the selected option
        preg_match('/^\[(\d+)\]/', $selectedOption, $matches);
        $selectedIndex = (int)$matches[1];
        
        // Apply the selected stash
        try {
            GitHelper::applyStash($selectedIndex);
        } catch (\Exception $e) {
            $io->error('Failed to apply stash: ' . $e->getMessage());
            return Command::FAILURE;
        }

        $io->success(sprintf('Successfully applied stash: %s', $stashes[$selectedIndex]));

        return Command::SUCCESS;
    }
}
