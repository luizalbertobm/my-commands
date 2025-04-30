<?php
namespace MyCommands\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use ZipArchive;
use MyCommands\Helper\ZipHelper;

#[AsCommand(name: 'zip:all', description: 'Compresses all files in the current directory where the command is executed')]
class ZipAllCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $cwd = getcwd();
        $targetDir = $cwd;

        $zipPath = $cwd . DIRECTORY_SEPARATOR . basename($cwd) . '.zip';

        // Use ZipHelper to perform zipping
        $helper = new ZipHelper();
        try {
            $helper->zipDirectory($targetDir, $zipPath);
        } catch (\RuntimeException $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        $io->success('Zip archive created at: ' . $zipPath);

        return Command::SUCCESS;
    }
}
