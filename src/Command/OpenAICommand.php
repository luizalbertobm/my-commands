<?php

namespace MyCommands\Command;

use MyCommands\Helper\EnvironmentHelper;
use MyCommands\Helper\GitHelper;
use MyCommands\Message;
use MyCommands\Service\EnvironmentService;
use MyCommands\Service\GitService;
use MyCommands\Service\OpenAIService;
use Psr\Http\Message\ResponseInterface;
use React\EventLoop\Loop;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Helper\ProgressIndicator;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'openai',
    description: 'Call OpenAI API to generate a response or a semantic git commit message.'
)]
class OpenAICommand extends Command
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
            ->addArgument('prompt', InputArgument::OPTIONAL, 'Text to send to OpenAI')
            ->addOption('model', 'm', InputOption::VALUE_REQUIRED, 'OpenAI model', OpenAIService::DEFAULT_MODEL)
            ->addOption('max-tokens', 't', InputOption::VALUE_REQUIRED, 'Maximum tokens', 600);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $apiKey = EnvironmentHelper::getEnvVar(OpenAIService::OPENAI_API_KEY);
        if (!$apiKey) {
            $this->io->error(Message::API_KEY_NOT_FOUND->value);
            $this->io->info(Message::API_KEY_INSTRUCTIONS->format(OpenAIService::OPENAI_API_KEY));
            $this->io->askHidden(Message::API_KEY_CREATE->value);
            $apiKey = EnvironmentHelper::getEnvVar(OpenAIService::OPENAI_API_KEY);
        }

        if (!$apiKey) {
            $this->io->error(Message::API_KEY_NOT_FOUND->value);
            return Command::FAILURE;
        }

        $openAIService = new OpenAIService(
            $apiKey,
            $output,
            OpenAIService::DEFAULT_MODEL,
            600
        );

        $prompt = (string)$input->getArgument('prompt');
        $commit = $prompt === 'commit';
        if (!$prompt) {
            $prompt = $this->io->ask('Enter prompt', Message::DEFAULT_PROMPT->value);
        }

        
        if ($commit) {
            try {
                $prompt = GitHelper::buildCommitPrompt();
            } catch (\RuntimeException $e) {
                if ($e->getCode() === Command::INVALID) {
                    $this->io->warning($e->getMessage());
                } else {
                    $this->io->error($e->getMessage());
                }

                return (int)$e->getCode();
            }
        }

        $responseData = $openAIService->processPrompt(
            $prompt,
            [
                'model' => $input->getOption('model'),
                'max_tokens' => (int)$input->getOption('max-tokens')
            ]
        );

        if (empty($responseData['choices'])) {
            $this->io->error(Message::NO_RESPONSE->value);
            return Command::FAILURE;
        }

        $this->processResponse($responseData, $commit);

        return Command::SUCCESS;
    }

    /**
     * Summary of processResponse
     * @param array<string, mixed> $data
     * @param string $prompt
     * @return void
     */
    private function processResponse(array $data, bool $commit): void
    {
        $this->io->section('Response');
        foreach ($data['choices'] as $choice) {
            $content = $choice['message']['content'] ?? '';
            $content = trim($content, characters: "`");
            $message = trim(preg_replace('/```(?:\w+)?\s*(.*?)\s*```/s', '$1', $content));
            $this->io->writeln($message);
            if ($commit) {
                if ($this->io->confirm(Message::COMMIT_CONFIRM->value, false)) {
                    GitHelper::commitAndPush($message, function ($output) {
                        $this->io->writeln($output);
                    });
                    $this->io->success(Message::COMMIT_SUCCESS->value);
                } else {
                    $this->io->note(Message::ACTION_CANCELED->value);
                }
            }
        }
    }
}
