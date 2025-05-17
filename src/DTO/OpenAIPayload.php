<?php

namespace MyCommands\DTO;

use MyCommands\Message;

class OpenAIPayload
{
    public function __construct(
        public readonly string $prompt,
        public readonly string $model = 'gpt-4-turbo',
        public readonly int $maxTokens = 600,
        public readonly float $temperature = 0.7,
        public readonly string $systemPrompt = 'You are a Linux terminal assistant.',
        public readonly bool $isCommit = false,
    ) {
    }

    /**
     * Converts the payload object to an array for API request.
     *
     * @return array<string, mixed> The API request payload
     */
    public function toArray(): array
    {
        $content = $this->isCommit
            ? Message::SYSTEM_ROLE_COMMIT->value
            : Message::SYSTEM_ROLE_ASK->value;

        return [
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $content],
                ['role' => 'user', 'content' => $this->prompt],
            ],
            'temperature' => $this->temperature,
            'max_tokens' => $this->maxTokens,
        ];
    }
}
