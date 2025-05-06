<?php

namespace MyCommands\Command;

use MyCommands\Helper\EnvironmentHelper;
use MyCommands\Service\OpenAIService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'ai:reset-key',
    description: 'Reset the OpenAI API key.'
)]
class AIResetKeyCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // get the shell profile file
        $shell = EnvironmentHelper::getShell();
        $io = new SymfonyStyle($input, $output);

        $io->section('Resetting OpenAI API Key');

        if (EnvironmentHelper::removeEnvVar(OpenAIService::OPENAI_API_KEY)) {
            $io->success('The OpenAI API key has been successfully reset.');

            // Perguntar ao usuário se deseja configurar uma nova chave
            if ($io->confirm('Do you want to set a new OpenAI API key now?', false)) {
                $newApiKey = $io->ask('Enter your OpenAI API key');

                if ($newApiKey) {
                    if (EnvironmentHelper::saveEnvVar(OpenAIService::OPENAI_API_KEY, $newApiKey)) {
                        $io->success('The new OpenAI API key has been successfully set.');
                    } else {
                        $io->error('Failed to set the new OpenAI API key.');
                    }
                } else {
                    $io->warning('No API key provided. The key remains unset.');
                }
            }

            // Use o método writeln com tags estilizadas para colorir o comando fonte
            $sourceCommand = "<fg=cyan>source {$shell}</>";
            $io->writeln("Reload your shell or run `{$sourceCommand}` (or equivalent) for the changes to take effect.");
        } else {
            $io->error('Failed to reset the OpenAI API key. It may not have been set.');
        }

        return Command::SUCCESS;
    }
}
