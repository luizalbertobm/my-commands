<?php

namespace MyCommands\Service;

use MyCommands\DTO\OpenAIPayload;
use MyCommands\DTO\OpenAIRequest;
use MyCommands\DTO\OpenAIRequestDTO;
use MyCommands\Message;
use Psr\Http\Message\ResponseInterface;
use React\EventLoop\Loop;
use React\Http\Browser;
use Symfony\Component\Console\Helper\ProgressIndicator;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

class OpenAIService
{
    public const DEFAULT_MODEL = 'gpt-4-turbo';
    private const API_URL = 'https://api.openai.com/v1/chat/completions';
    private const SPINNER_CHARS = ['⠏', '⠛', '⠹', '⢸', '⣰', '⣤', '⣆', '⡇'];

    public const OPENAI_API_KEY = 'OPENAI_API_KEY';

    private Browser $browser;

    public function __construct(
        private string $apiKey,
        private OutputInterface $output,
        private string $model = self::DEFAULT_MODEL,
        private int $maxTokens = 600
    ) {
        $this->browser = new Browser(Loop::get());
    }

    /**
     * Summary of processPrompt
     * @param string $prompt
     * @param array<string, mixed> $options
     * @return array<string, mixed> The OpenAI API response data
     */
    public function processPrompt(string $prompt, array $options = []): array
    {
        $payload = new OpenAIPayload(
            prompt: $prompt,
            model: $options['model'] ?? $this->model,
            maxTokens: $options['max_tokens'] ?? $this->maxTokens,
            temperature: $options['temperature'] ?? 0.7
        );

        return $this->sendRequest($payload->toArray());
    }

    /**
     * Sends a request to the OpenAI API
     *
     * @param array<string, mixed> $payload The OpenAI API request payload
     * @return array<string, mixed> The OpenAI API response
     * @throws \RuntimeException When the request fails or returns an error
     */
    private function sendRequest(array $payload): array
    {
        $loop = Loop::get();
        $header = [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ];

        $indicator = new ProgressIndicator($this->output, 'verbose', 100, self::SPINNER_CHARS);
        $indicator->start(Message::CONNECTING->value);
        $loop->addPeriodicTimer(0.2, fn () => $indicator->advance());

        $responseData = null;

        $jsonPayload = json_encode($payload);
        if ($jsonPayload === false) {
            throw new \RuntimeException('Failed to encode request payload: ' . json_last_error_msg());
        }

        $this->browser
            ->post(self::API_URL, $header, $jsonPayload)
            ->then(
                function (ResponseInterface $response) use (&$responseData, $indicator, $loop) {
                    $indicator->finish('Request completed');
                    $body = (string)$response->getBody();
                    $data = json_decode($body, true) ?: [];
                    $this->renderTokensTable($data['usage'] ?? []);
                    if (isset($data['error'])) {
                        throw new \RuntimeException($data['error']['message']);
                    }
                    $responseData = $data;
                    $loop->stop();
                },
                function (\Throwable $e) use (&$responseData, $loop) {
                    $responseData = null;
                    $loop->stop();
                    throw new \RuntimeException(
                        Message::API_REQUEST_FAILED->format($e->getMessage())
                    );
                }
            );

        $loop->run();

        if (!is_array($responseData)) {
            throw new \RuntimeException('Failed to get valid response');
        }

        return $responseData;
    }

    /**
     * Renders a table with token usage information
     *
     * @param array<string, mixed> $usage The token usage data
     */
    private function renderTokensTable(array $usage): void
    {
        if (empty($usage)) {
            return;
        }

        $table = new Table($this->output);
        $table
            ->setHeaders(['Type', 'Tokens'])
            ->setRows([
                ['Model', $this->model],
                ['Max Tokens', $this->maxTokens],
                ['Temperature', 0.7],
                ['Prompt Tokens', $usage['prompt_tokens'] ?? 0],
                ['Completion Tokens', $usage['completion_tokens'] ?? 0],
                ['Total Tokens', $usage['total_tokens'] ?? 0]
            ]);
        $table->render();
    }
}
