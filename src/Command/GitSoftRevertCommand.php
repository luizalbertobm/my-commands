<?php

namespace MyCommands\Command;

use MyCommands\Helper\GitHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'git:revert',
    description: 'Soft revert the last commit, keeping changes in the working directory.'
)]
class GitSoftRevertCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Git Soft Revert Command');

        try {
            GitHelper::softRevertLastCommit();
            $io->success('The last commit has been successfully soft reverted. Changes are still in the working directory.');
        } catch (\Exception $e) {
            $io->error('Failed to soft revert the last commit: '.$e->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
