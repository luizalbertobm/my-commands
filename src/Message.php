<?php

namespace MyCommands;

enum Message: string
{
    case HELP = 'Use this command to send a prompt to OpenAI or generate a git commit message from current diff. Use the semantic commit message format.';
    case API_KEY_NOT_FOUND = 'OpenAI API key not found.';
    case API_KEY_INSTRUCTIONS = 'Create you API key at https://platform.openai.com/api-keys.';
    case API_KEY_CREATE = 'Type your OpenAI API key';
    case EMPTY_PROMPT = 'Empty prompt received, using default help request.';
    case ENTER_PROMPT = 'Please type your request and press Enter';
    case DEFAULT_PROMPT = 'What is the brazilian capital?';
    case COMMIT_ERR = 'Failed to generate commit message: %s';
    case NO_CHANGES = 'No changes detected to generate a commit message.';
    case GIT_UNAVAILABLE = 'Git is not available on this system.';
    case SYSTEM_ROLE_COMMIT = 'You are an expert in generating commit messages following the Conventional Commits standard. Always respond only with the commit message, in the format: <type>(<optional scope>): <imperative summary up to 50 characters>, and, if necessary, an optional body separated by a blank line. Do not include any other information';
    case SYSTEM_ROLE_ASK = 'You are a console assistent. the user will ask you questions and you will answer them. Take in consideration that the output will be used in a terminal';
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
    case COMMIT_PROMPT = 'using {language} language, write a plain text for a concise semantic conventional commit based on the following diff:';
    case API_REQUEST_FAILED = 'API request failed: %s';

    /**
     * Format message with sprintf.
     */
    public function format(mixed ...$args): string
    {
        return sprintf($this->value, ...$args);
    }
}
