<?php

namespace MyCommands\Command;

use MyCommands\Helper\EnvironmentHelper;
use MyCommands\Helper\GitHelper;
use MyCommands\Language;
use MyCommands\Message;
use MyCommands\Service\OpenAIService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'ai:commit',
    description: 'Uses AI to generate git commit message based on diff.'
)]
class AICommitCommand extends Command
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

        if ($input->getOption('reset')) {
            $this->io->note('Resetting env variable:'.OpenAIService::OPENAI_API_KEY);
            EnvironmentHelper::removeEnvVar(
                OpenAIService::OPENAI_API_KEY,
            );
            $this->io->success('Env variable removed');

            return Command::SUCCESS;
        }
        $apiKey = $this->getOrSetApiKey();

        $language = $this->io->choice(
            'In which language do you want to make the commit?',
            Language::getAllLanguages(),
            Language::ENGLISH->value,
        );

        $this->io->note("Selected language: $language");

        $openAIService = new OpenAIService(
            $apiKey,
            $output,
            OpenAIService::DEFAULT_MODEL,
            600
        );

        try {
            $prompt = GitHelper::buildCommitPrompt($language);
        } catch (\RuntimeException $e) {
            if (Command::INVALID === $e->getCode()) {
                $this->io->warning($e->getMessage());
            } else {
                $this->io->error($e->getMessage());
            }

            return (int) $e->getCode();
        }

        $responseData = $openAIService->processPrompt(
            $prompt,
            [
                'model' => $input->getOption('model'),
                'max_tokens' => (int) $input->getOption('max-tokens'),
            ],
            true,
        );

        if (empty($responseData['choices'])) {
            $this->io->error(Message::NO_RESPONSE->value);

            return Command::FAILURE;
        }

        $this->processResponse($responseData);

        return Command::SUCCESS;
    }

    /**
     * Summary of processResponse.
     *
     * @param array<string, mixed> $data
     */
    private function processResponse(array $data): void
    {
        $this->io->section('Commit message');
        foreach ($data['choices'] as $choice) {
            $message = $choice['message']['content'] ?? '';
            $message = $this->cleanMessage($message);
            $this->io->writeln($message);

            if ($this->io->confirm(Message::COMMIT_CONFIRM->value, false)) {
                GitHelper::commitAndPush($message, function ($type, $buffer) {
                    if (!empty(trim($buffer))) {
                        $this->io->write($buffer);
                    }
                });
                $this->io->success(Message::COMMIT_SUCCESS->value);
            } else {
                $this->io->note(Message::ACTION_CANCELED->value);
            }
        }
    }

    /**
     * Clean the message by removing unwanted characters.
     *
     * @param string $message The message to clean
     *
     * @return string The cleaned message
     */
    private function cleanMessage(string $message): string
    {
        if (empty($message)) {
            return '';
        }

        // Primeiro remover blocos de código completos se existirem
        $result = preg_replace('/^```[\w]*\s*([\s\S]*?)\s*```$/m', '$1', $message);
        // Garantir que o resultado é string
        $result = null === $result ? $message : $result;

        // Remover outros caracteres especiais comuns no início e fim
        $result = preg_replace('/^[\s`*#>_~\-+:"\']*/', '', $result);
        $result = null === $result ? $message : $result;

        $result = preg_replace('/[\s`*#>_~\-+:"\']*$/', '', $result);
        $result = null === $result ? $message : $result;

        // Finalmente um trim simples para espaços extras
        return trim($result);
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
        }

        return $apiKey;
    }
}
