<?php

namespace MyCommands\Command;

use MyCommands\Helper\DockerHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'docker:stop-all',
    description: 'Stop all running Docker containers.'
)]
class DockerStopAllCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $dockerHelper = new DockerHelper();
        try {
            $rows = $dockerHelper->getContainerRows();
            $ids = $dockerHelper->getContainerIds();
        } catch (\RuntimeException $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        if (empty($ids)) {
            $io->info('No containers are running.');

            return Command::SUCCESS;
        }

        $io->section('Running containers:');
        $io->table(['#', 'ID', 'Name', 'Image', 'Ports'], $rows);

        if (!$io->confirm('Do you want to stop all containers?', true)) {
            $io->warning('Operation cancelled.');

            return Command::SUCCESS;
        }

        try {
            $stopped = $dockerHelper->stopContainers($ids);
        } catch (\RuntimeException $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }
        $io->success('Stopped containers: '.implode(', ', $stopped));

        return Command::SUCCESS;
    }
}
