<?php

namespace MyCommands\Command;

use MyCommands\Helper\DockerHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'docker:list',
    description: 'Lists running Docker containers and allows accessing bash in one of them.'
)]
class DockerListCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $dockerHelper = new DockerHelper();
        try {
            $rows = $dockerHelper->getContainerRows();
        } catch (\RuntimeException $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        if (empty($rows)) {
            $io->info('No running Docker containers.');

            return Command::SUCCESS;
        }

        $io->section('Running containers:');
        $io->table(['#', 'ID', 'Name', 'Image', 'Ports'], $rows);

        return Command::SUCCESS;
    }
}
