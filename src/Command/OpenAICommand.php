<?php

namespace MyCommands\Command;

use MyCommands\Message;
use Psr\Http\Message\ResponseInterface;
use React\EventLoop\Loop;
use React\Http\Browser;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Helper\ProgressIndicator;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'openai',
    description: 'Call OpenAI API to generate a response or a semantic git commit message.'
)]
class OpenAICommand extends Command
{
    private const API_URL       = 'https://api.openai.com/v1/chat/completions';
    private const DEFAULT_MODEL = 'gpt-4-turbo';
    private const ENV_API_KEY   = 'OPENAI_API_KEY';
    private const SPINNER_CHARS = ['⠏', '⠛', '⠹', '⢸', '⣰', '⣤', '⣆', '⡇'];

    private Browser $browser;
    private SymfonyStyle $io;

    public function __construct()
    {
        parent::__construct();
        // Inicializa o ReactPHP Browser com o Loop padrão
        $this->browser = new Browser(Loop::get());
    }

    protected function configure(): void
    {
        $this
            ->setHelp(Message::HELP->value)
            ->addArgument('prompt', InputArgument::OPTIONAL, 'Text to send to OpenAI')
            ->addOption('model', 'm', InputOption::VALUE_REQUIRED, 'OpenAI model', self::DEFAULT_MODEL)
            ->addOption('max-tokens', 't', InputOption::VALUE_REQUIRED, 'Maximum tokens', 600);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);
        $apiKey = $this->getApiKey();

        if (!$apiKey) {
            return Command::FAILURE;
        }

        $prompt = $this->resolvePrompt($input);

        if ($prompt === 'commit') {
            try {
                $commitPrompt = $this->buildCommitPrompt();
            } catch (\RuntimeException $e) {
                if ($e->getCode() === Command::INVALID) {
                    $this->io->warning($e->getMessage());
                    return Command::INVALID;
                }
                $this->io->error($e->getMessage());
                return Command::FAILURE;
            }

            if ($commitPrompt === Message::NO_CHANGES->value) {
                $this->io->comment($commitPrompt);
                return Command::SUCCESS;
            }

            $prompt = $commitPrompt;
        }

        try {
            $data = $this->requestOpenAIAsync(
                $apiKey,
                $prompt,
                (string)$input->getOption('model'),
                (int)$input->getOption('max-tokens')
            );
        } catch (\RuntimeException $e) {
            $this->io->error('OpenAI request failed: ' . $e->getMessage());
            return Command::FAILURE;
        }

        $this->processResponse($data, $prompt);

        return Command::SUCCESS;
    }

    /**
     * Retrieves the OpenAI API key from the environment.
     *
     * @return string|null The API key or null if not found
     */
    private function getApiKey(): ?string
    {
        $key = getenv(self::ENV_API_KEY) ?: $_SERVER[self::ENV_API_KEY] ?? null;

        if (!$key) {
            $this->io->error(Message::API_KEY_NOT_FOUND->value);
            $this->io->info(Message::API_KEY_INSTRUCTIONS->format(self::ENV_API_KEY));
            $key = $this->io->ask(Message::API_KEY_CREATE->value, null, function ($input) {
                if (empty($input)) {
                    throw new \RuntimeException('API key cannot be empty.');
                }
                return $input;
            });

            // Adiciona a chave às variáveis de ambiente do sistema
            putenv(self::ENV_API_KEY . '=' . $key);
            $_SERVER[self::ENV_API_KEY] = $key;

            // Determina o shell do usuário
            $shell = getenv('SHELL') ?: '/bin/bash';
            $envFile = match (basename($shell)) {
                'zsh' => $_SERVER['HOME'] . '/.zshrc',
                'bash' => $_SERVER['HOME'] . '/.bashrc',
                default => null,
            };

            // Persistir a chave no arquivo de ambiente do shell, se possível
            if ($envFile && is_writable($envFile)) {
                $envContent = file_get_contents($envFile);
                if (strpos($envContent, "export " . self::ENV_API_KEY . "=") !== false) {
                    $this->io->warning("API key already exists in $envFile. Skipping addition.");
                    return $key;
                }
                file_put_contents($envFile, "\nexport " . self::ENV_API_KEY . "=\"$key\"\n", FILE_APPEND);
                $this->io->success("API key saved to $envFile.");
            } else {
                $this->io->warning('Failed to persist API key. Add it manually to your environment variables.');
            }
        }

        return $key;
    }

    /**
     * Resolves the prompt to use for the OpenAI request.
     *
     * @param InputInterface $input The input interface
     * @return string The resolved prompt
     */
    private function resolvePrompt(InputInterface $input): string
    {
        $prompt = (string)$input->getArgument('prompt');
        if (!$prompt) {
            $prompt = $this->io->ask('Enter prompt', Message::DEFAULT_PROMPT->value);
        }
        return $prompt;
    }

    /**
     * Builds a prompt for generating a semantic commit message.
     *
     * @return string The generated commit prompt
     * @throws \RuntimeException If Git is unavailable
     */
    private function buildCommitPrompt(): string
    {
        if (!$this->isGitAvailable()) {
            throw new \RuntimeException(Message::GIT_UNAVAILABLE->value);
        }

        $diff = $this->getGitDiff('--staged');
        if (empty(trim($diff))) {
            $diff = $this->getGitDiff('');
            if (empty(trim($diff))) {
                throw new \RuntimeException(Message::NO_CHANGES->value, Command::INVALID);
            }
        }

        return Message::COMMIT_PROMPT->value . "\n" . $diff;
    }

    /**
     * Checks if Git is available on the system.
     *
     * @return bool True if Git is available, false otherwise
     */
    private function isGitAvailable(): bool
    {
        $process = new Process(['git', '--version']);
        $process->run();
        return $process->isSuccessful();
    }

    /**
     * Retrieves the Git diff for the specified mode.
     *
     * @param string $mode The diff mode (e.g., '--staged')
     * @return string The Git diff output
     */
    private function getGitDiff(string $mode): string
    {
        $cmd = $mode ? ['git', 'diff', $mode] : ['git', 'diff'];
        $process = new Process($cmd);
        $process->run();
        return $process->getOutput();
    }

    /**
     * Sends an asynchronous request to the OpenAI API.
     *
     * @param string $key The API key
     * @param string $prompt The prompt to send
     * @param string $model The OpenAI model to use
     * @param int $maxTokens The maximum number of tokens
     * @return array The response data
     * @throws \RuntimeException If the request fails
     */
    private function requestOpenAIAsync(
        string $key,
        string $prompt,
        string $model,
        int $maxTokens
    ): array {
        $loop   = Loop::get();
        $header = [
            'Authorization' => 'Bearer ' . $key,
            'Content-Type'  => 'application/json'
        ];
        $payload = json_encode([
            'model'       => $model,
            'messages'    => [
                ['role' => 'system', 'content' => Message::SYSTEM_ROLE->value],
                ['role' => 'user',   'content' => $prompt]
            ],
            'temperature' => 0.7,
            'max_tokens'  => $maxTokens
        ]);

        $indicator = new ProgressIndicator($this->io, 'verbose', 100, self::SPINNER_CHARS);
        $indicator->start(Message::CONNECTING->value);
        $loop->addPeriodicTimer(0.2, fn() => $indicator->advance());

        $responseData = null;
        $this->browser
            ->post(self::API_URL, $header, $payload)
            ->then(
                function (ResponseInterface $response) use (&$responseData, $indicator, $loop) {
                    $indicator->finish('Request completed');
                    $body = (string)$response->getBody();
                    $data = json_decode($body, true) ?: [];
                    if (isset($data['error'])) {
                        throw new \RuntimeException($data['error']['message']);
                    }
                    $responseData = $data;
                    $loop->stop();
                },
                function (\Throwable $e) use (&$responseData, $loop) {
                    $this->io->error('Request error: ' . $e->getMessage());
                    $responseData = null;
                    $loop->stop();
                }
            );

        $loop->run();

        if (!is_array($responseData)) {
            throw new \RuntimeException('Failed to get valid response');
        }

        return $responseData;
    }

    /**
     * Renders the tokens usage information in a table format.
     *
     * @param array $usage The usage data containing token counts
     */
    private function renderTokensTable(array $usage): void
    {
        $table = new Table($this->io);
        $table
            ->setHeaders(['Type', 'Tokens'])
            ->setRows([
                ['Prompt', $usage['prompt_tokens'] ?? 0],
                ['Completion', $usage['completion_tokens'] ?? 0],
                ['Total', $usage['total_tokens'] ?? 0],
            ]);
        $table->render();
    }

    /**
     * Processes the OpenAI API response and displays it.
     *
     * @param array $data The response data
     * @param string $prompt The original prompt
     */
    private function processResponse(array $data, string $prompt): void
    {
        if (isset($data['usage'])) {
            $this->renderTokensTable($data['usage']);
        }
        
        // check if it is a commit
        if (str_contains($prompt, 'commit')) {
            $this->io->section('Commit message:');
        } else {
            $this->io->section('Response:');
        }
        
        foreach ($data['choices'] as $choice) {
            $content = $choice['message']['content'] ?? '';
            $message = trim(preg_replace('/```(?:\w+)?\s*(.*?)\s*```/s', '$1', $content));
            $this->io->text($message);

            // check if the argument prompt is 'commit'
            if (str_contains($prompt, 'commit')) {
                $confirmCommit = $this->io->confirm(Message::COMMIT_CONFIRM->value, false);
                if ($confirmCommit) {
                    $this->executeGitCommands($message);
                    $this->io->success(Message::COMMIT_SUCCESS->value);
                } else {
                    $this->io->note(Message::ACTION_CANCELED->value);
                }
            }
        }
    }

    /**
     * Executes Git commands to add, commit, and push changes.
     *
     * @param string $msg The commit message
     * @throws ProcessFailedException If a Git command fails
     */
    private function executeGitCommands(string $msg): void
    {
        foreach (
            [['git', 'add', '.'], ['git', 'commit', '-m', $msg], ['git', 'push']]
            as $cmd
        ) {
            $process = new Process($cmd);
            $process->run(fn($type, $buffer) => $this->io->text(trim($buffer)));
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
        }
    }
}
