<?php

namespace MyCommands\Command;

use MyCommands\Helper\EnvironmentHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'env:unset',
    description: 'Unset an environment variable from the shell profile file.'
)]
class EnvUnsetCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->section('Unsetting an Environment Variable');

        // Ask for the environment variable name
        $envVarName = $io->ask('Enter the name of the environment variable to unset');
        if (!$envVarName) {
            $io->error('Environment variable name cannot be empty.');

            return Command::FAILURE;
        }

        // Get shell profile file
        $shell = EnvironmentHelper::getShell();

        // Remove the environment variable using EnvironmentHelper
        if (EnvironmentHelper::removeEnvVar($envVarName)) {
            $io->success("The environment variable '$envVarName' has been successfully unset.");
            $io->info([
                "Run `unset $envVarName` in your terminal to remove it from the current session.",
            ]);
        } else {
            $io->error('Failed to unset the environment variable.');

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
