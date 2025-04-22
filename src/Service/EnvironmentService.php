<?php

namespace MyCommands\Service;

class EnvironmentService
{
    public static function getEnvVar(string $envVarName): ?string
    {
        return getenv($envVarName) ?: $_SERVER[$envVarName] ?? null;
    }

    public static function saveEnvVar(string $envVarName, string $key): bool
    {
        $shell = self::getShell();

        if ($shell) {
            if (self::isEnvVarInShellFile($envVarName, $shell)) {
                return false;
            }

            file_put_contents($shell, "export $envVarName='$key'\n", FILE_APPEND);
            self::reloadShell($shell);

            return true;
        }

        return false;
    }

    public static function isEnvVarInShellFile(string $envVarName, string $shellFile): bool
    {
        if (!file_exists($shellFile)) {
            return false;
        }

        $content = file_get_contents($shellFile);
        if ($content === false) {
            return false;
        }
        return strpos($content, "export $envVarName=") !== false;
    }

    private static function getShell(): ?string
    {
        if (file_exists('~/.bash_profile')) {
            return '~/.bash_profile';
        } elseif (file_exists('~/.bashrc')) {
            return '~/.bashrc';
        } elseif (file_exists('~/.zshrc')) {
            return '~/.zshrc';
        }
        return null;
    }

    private static function reloadShell(string $profileFile): void
    {
        $shell = getenv('SHELL') ?: '/bin/bash';
        $command = match (basename($shell)) {
            'zsh' => "source $profileFile",
            'bash' => "source $profileFile",
            default => null,
        };

        if ($command) {
            shell_exec($command);
        }
    }
}
