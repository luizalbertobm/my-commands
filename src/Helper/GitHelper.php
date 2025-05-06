<?php

// src/Service/GitService.php

namespace MyCommands\Helper;

use MyCommands\Message;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class GitHelper
{
    public static function isGitAvailable(): bool
    {
        $process = new Process(['git', '--version']);
        $process->run();

        return $process->isSuccessful();
    }

    public static function getDiff(string $mode = ''): string
    {
        $cmd = $mode ? ['git', 'diff', $mode] : ['git', 'diff'];
        $process = new Process($cmd);
        $process->run();

        return $process->getOutput();
    }

    public static function commitAndPush(string $message, ?callable $outputCallback = null): void
    {
        // Lógica para commit e push (movida do método executeGitCommands)
        $process = new Process(['git', 'add', '.']);
        $process->run($outputCallback);
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $process = new Process(['git', 'commit', '-m', $message]);
        $process->run($outputCallback);
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $process = new Process(['git', 'push']);
        $process->run($outputCallback);
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    public static function buildCommitPrompt(?string $lang = 'English'): string
    {
        if (!self::isGitAvailable()) {
            throw new \RuntimeException(Message::GIT_UNAVAILABLE->value);
        }

        $diff = self::getDiff('--staged');
        if (empty(trim($diff))) {
            $diff = self::getDiff('');
            if (empty(trim($diff))) {
                throw new \RuntimeException(Message::NO_CHANGES->value, Command::INVALID);
            }
        }

        $commitPrompt = Message::COMMIT_PROMPT->value;
        if ($lang) {
            $commitPrompt = str_replace('{language}', $lang, $commitPrompt);
        }

        return $commitPrompt."\n".$diff;
    }

    public static function stashChanges(?string $comment = null): void
    {
        if (!self::isGitAvailable()) {
            throw new \RuntimeException(Message::GIT_UNAVAILABLE->value);
        }

        $cmd = ['git', 'stash', 'push'];
        if ($comment) {
            $cmd[] = '-m';
            $cmd[] = $comment;
        }

        $process = new Process($cmd);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    public static function applyStash(int $index): void
    {
        if (!self::isGitAvailable()) {
            throw new \RuntimeException(Message::GIT_UNAVAILABLE->value);
        }

        $process = new Process(['git', 'stash', 'apply', "stash@{{$index}}"]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    public static function dropStash(int $index): void
    {
        if (!self::isGitAvailable()) {
            throw new \RuntimeException(Message::GIT_UNAVAILABLE->value);
        }

        $process = new Process(['git', 'stash', 'drop', "stash@{$index}"]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    /**
     * List all stashes.
     *
     * @return array<string> an array of stash entries
     *
     * @throws \RuntimeException if Git is not available or the command fails
     */
    public static function listStashes(): array
    {
        if (!self::isGitAvailable()) {
            throw new \RuntimeException(Message::GIT_UNAVAILABLE->value);
        }

        $process = new Process(['git', 'stash', 'list']);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return explode("\n", trim($process->getOutput()));
    }

    public static function softRevertLastCommit(): void
    {
        if (!self::isGitAvailable()) {
            throw new \RuntimeException(Message::GIT_UNAVAILABLE->value);
        }

        $process = new Process(['git', 'reset', '--soft', 'HEAD~1']);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    public static function stageChanges(?string $file = null): void
    {
        if (!self::isGitAvailable()) {
            throw new \RuntimeException(Message::GIT_UNAVAILABLE->value);
        }

        $cmd = ['git', 'add'];
        if ($file) {
            $cmd[] = $file;
        } else {
            $cmd[] = '.';
        }
        $process = new Process($cmd);

        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    public static function unstageChanges(?string $file = null): void
    {
        if (!self::isGitAvailable()) {
            throw new \RuntimeException(Message::GIT_UNAVAILABLE->value);
        }
        $cmd = ['git', 'reset'];
        if ($file) {
            $cmd[] = $file;
        } else {
            $cmd[] = '.';
        }
        $process = new Process($cmd);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }
}
