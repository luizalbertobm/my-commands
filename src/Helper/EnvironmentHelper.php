<?php

// src/Service/EnvironmentService.php

namespace MyCommands\Helper;

class EnvironmentHelper
{
    /**
     * Get an environment variable from the system or server.
     * This method checks both the environment variables and the server variables.
     * @param string $envVarName
     * @return string|null
     */
    public static function getEnvVar(string $envVarName): ?string
    {
        return getenv($envVarName) ?: $_SERVER[$envVarName] ?? null;
    }

    /**
     * Save an environment variable to the user's shell profile file.
     * This method appends the export command to the shell profile file.
     * @param string $envVarName
     * @param string $key
     * @return bool
     */
    public static function saveEnvVar(string $envVarName, string $key): bool
    {
        $shell = self::getShell();

        if ($shell) {
            if (!self::isEnvVarInShellFile($envVarName, $shell)) {
                file_put_contents($shell, "export $envVarName='$key'\n", FILE_APPEND);
            }

            putenv("$envVarName=$key");
            $_SERVER[$envVarName] = $key;

            $test = getenv($envVarName);
            error_log("$envVarName=$test");
            return true;
        }

        return false;
    }

    /**
     * Check if the environment variable is already in the shell profile file.
     * @param string $envVarName
     * @param string $shellFile
     * @return bool
     */
    private static function isEnvVarInShellFile(string $envVarName, string $shellFile): bool
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

    /**
     * Get the shell profile file based on the user's environment.
     * This method checks for common shell profile files like .bash_profile, .bashrc, and .zshrc.
     * It returns the first one found or null if none are found.
     * @return string|null
     */
    private static function getShell(): ?string
    {
        // Obter o diretório home do usuário atual
        $homeDir = getenv('HOME') ?: (getenv('USERPROFILE') ?: '~');

        $files = [
            "$homeDir/.zshrc",
            "$homeDir/.bashrc",
            "$homeDir/.bash_profile",
        ];

        foreach ($files as $file) {
            if (file_exists($file)) {
                return $file;
            }
        }

        return null;
    }
}
