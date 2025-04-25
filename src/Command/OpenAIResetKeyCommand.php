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
class OpenAIResetKeyCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->section('Resetting OpenAI API Key');

        if (EnvironmentHelper::removeEnvVar(OpenAIService::OPENAI_API_KEY)) {
            $io->success('The OpenAI API key has been successfully reset.');
        } else {
            $io->error('Failed to reset the OpenAI API key. It may not have been set.');
        }

        return Command::SUCCESS;
    }
}
