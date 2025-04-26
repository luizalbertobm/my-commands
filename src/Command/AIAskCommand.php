<?php

namespace MyCommands\Command;

use MyCommands\Helper\EnvironmentHelper;
use MyCommands\Message;
use MyCommands\Service\OpenAIService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'ai:ask',
    description: 'Uses AI to respond to a prompt or question.'
)]
class AIAskCommand extends Command
{
    private SymfonyStyle $io;
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setHelp(Message::HELP->value)
            ->addOption('reset', 'r', InputOption::VALUE_NONE, 'Reset the OpenAI API key')
            ->addOption('model', 'm', InputOption::VALUE_REQUIRED, 'OpenAI model', OpenAIService::DEFAULT_MODEL)
            ->addOption('max-tokens', 't', InputOption::VALUE_REQUIRED, 'Maximum tokens', 600);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $apiKey = $this->getOrSetApiKey();

        $openAIService = new OpenAIService(
            $apiKey,
            $output,
            OpenAIService::DEFAULT_MODEL,
            600
        );

        $prompt = null;
        if (!$prompt) {
            $prompt = $this->io->ask(Message::ENTER_PROMPT->value, Message::DEFAULT_PROMPT->value);
        }

        $responseData = $openAIService->processPrompt(
            $prompt,
            [
                'model' => $input->getOption('model'),
                'max_tokens' => (int)$input->getOption('max-tokens'),
            ]
        );

        if (empty($responseData['choices'])) {
            $this->io->error(Message::NO_RESPONSE->value);
            return Command::FAILURE;
        }

        $this->processResponse($responseData);

        return Command::SUCCESS;
    }

    private function getOrSetApiKey(): string
    {
        $apiKey = EnvironmentHelper::getEnvVar(OpenAIService::OPENAI_API_KEY);
        if (!$apiKey) {
            $this->io->error(Message::API_KEY_NOT_FOUND->value);
            $this->io->note(Message::API_KEY_INSTRUCTIONS->format(OpenAIService::OPENAI_API_KEY));
            $apiKey = $this->io->ask(Message::API_KEY_CREATE->value);
            EnvironmentHelper::saveEnvVar(
                OpenAIService::OPENAI_API_KEY,
                $apiKey,
            );
            $apiKey = EnvironmentHelper::getEnvVar(OpenAIService::OPENAI_API_KEY);
        }

        if (!$apiKey) {
            throw new \RuntimeException(Message::API_KEY_NOT_FOUND->value);
        };

        return $apiKey;
    }

    /**
     * Summary of processResponse.
     * @param  array<string, mixed> $data
     * @return void
     */
    private function processResponse(array $data): void
    {
        $this->io->section('Response');
        foreach ($data['choices'] as $choice) {
            $message = $choice['message']['content'] ?? '';
            $this->io->writeln($message);
        }
    }

}
