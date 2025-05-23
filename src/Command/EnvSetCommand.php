<?php

namespace MyCommands\Command;

use MyCommands\Helper\EnvironmentHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'env:set',
    description: 'Set an environment variable in the shell profile file.'
)]
class EnvSetCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->section('Setting an Environment Variable');

        $envVarName = $io->ask('Enter the name of the environment variable');
        if (!$envVarName) {
            $io->error('Environment variable name cannot be empty.');

            return Command::FAILURE;
        }

        $envVarValue = $io->ask('Enter the value of the environment variable');
        if (!$envVarValue) {
            $io->error('Environment variable value cannot be empty.');

            return Command::FAILURE;
        }

        $shell = EnvironmentHelper::getShell();

        if (EnvironmentHelper::saveEnvVar($envVarName, $envVarValue)) {
            $io->success("The environment variable '$envVarName' with value '$envVarValue' has been successfully set.");
            $io->info("Restart your terminal or run `source $shell` (or equivalent) for the changes to take effect.");
        } else {
            $io->error('Failed to set the environment variable.');

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
