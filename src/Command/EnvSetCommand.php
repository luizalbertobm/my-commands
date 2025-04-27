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

        // Ask for the environment variable name
        $envVarName = $io->ask('Enter the name of the environment variable');
        if (!$envVarName) {
            $io->error('Environment variable name cannot be empty.');
            return Command::FAILURE;
        }

        // Ask for the environment variable value
        $envVarValue = $io->ask('Enter the value of the environment variable');
        if (!$envVarValue) {
            $io->error('Environment variable value cannot be empty.');
            return Command::FAILURE;
        }

        // Save the environment variable using EnvironmentHelper
        if (EnvironmentHelper::saveEnvVar($envVarName, $envVarValue)) {
            $io->success("The environment variable '$envVarName' has been successfully set.");
            $io->info('Reload your shell or restart your terminal for the changes to take effect.');
        } else {
            $io->error('Failed to set the environment variable.');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
