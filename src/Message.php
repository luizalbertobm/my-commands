<?php

namespace MyCommands;

enum Message: string
{
    case HELP = 'Use this command to send a prompt to OpenAI or generate a git commit message from current diff. Use the semantic commit message format.';
    case API_KEY_NOT_FOUND = 'OpenAI API key not found.';
    case API_KEY_INSTRUCTIONS = 'Create you API key at https://platform.openai.com/api-keys.';
    case API_KEY_CREATE = 'Type your OpenAI API key';
    case ENTER_PROMPT = 'Please type your request and press Enter';
    case EMPTY_PROMPT = 'Empty prompt received, using default help request.';
    case DEFAULT_PROMPT = 'What is the brazilian capital?';
    case COMMIT_ERR = 'Failed to generate commit message: %s';
    case NO_CHANGES = 'No changes detected to generate a commit message.';
    case GIT_UNAVAILABLE = 'Git is not available on this system.';
    case SYSTEM_ROLE = 'You are a Linux terminal assistant.';
    case CONNECTING = 'Processing your prompt, please wait...';
    case REQUEST_COMPLETED = 'Request completed';
    case UNKNOWN_ERROR = 'Unknown API error';
    case API_ERROR = 'OpenAI API error: %s';
    case NO_RESPONSE = 'No response content received from OpenAI.';
    case EMPTY_RESPONSE = 'Empty response';
    case TOKENS_INFO = 'Tokens: %d prompt + %d completion = %d total';
    case COMMIT_CONFIRM = 'Stage changes, commit with this message and push?';
    case COMMIT_SUCCESS = 'Changes staged, committed, and pushed successfully.';
    case GIT_CMD_ERROR = 'Failed to run git command: %s';
    case ACTION_CANCELED = 'Action canceled by user.';
    case COMMIT_PROMPT = 'Write only the plain taext from a concise semantic commit message based on the following diff:';
    case API_REQUEST_FAILED = 'API request failed: %s';

    /**
     * Format message with sprintf
     *
     * @param mixed ...$args
     */
    public function format(mixed ...$args): string
    {
        return sprintf($this->value, ...$args);
    }
}
