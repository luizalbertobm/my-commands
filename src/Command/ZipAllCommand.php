<?php

namespace MyCommands\Command;

use MyCommands\Helper\ZipHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'zip:all', description: 'Compresses all files in the current directory where the command is executed')]
class ZipAllCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $cwd = getcwd();

        // Check if getcwd() returned false
        if (false === $cwd) {
            $io->error('Failed to determine the current working directory.');

            return Command::FAILURE;
        }

        $targetDir = $cwd;

        $zipPath = $cwd.DIRECTORY_SEPARATOR.basename($cwd).'.zip';

        // Use ZipHelper to perform zipping
        $helper = $this->getZipHelper();
        try {
            $helper->zipDirectory($targetDir, $zipPath);
        } catch (\RuntimeException $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        $io->success('Zip archive created at: '.$zipPath);

        return Command::SUCCESS;
    }

    /**
     * Get a ZipHelper instance
     * This method can be overridden in tests.
     */
    protected function getZipHelper(): ZipHelper
    {
        return new ZipHelper();
    }
}
