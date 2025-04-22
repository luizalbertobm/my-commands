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
        public readonly string $systemPrompt = 'You are a Linux terminal assistant.'
    ) {
    }

    /**
     * Converts the payload object to an array for API request
     *
     * @return array<string, mixed> The API request payload
     */
    public function toArray(): array
    {
        return [
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => Message::SYSTEM_ROLE->value],
                ['role' => 'user', 'content' => $this->prompt]
            ],
            'temperature' => $this->temperature,
            'max_tokens' => $this->maxTokens
        ];
    }
}
