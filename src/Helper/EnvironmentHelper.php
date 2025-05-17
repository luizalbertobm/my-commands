<?php

// src/Service/EnvironmentService.php

namespace MyCommands\Helper;

class EnvironmentHelper
{
    /**
     * Get an environment variable from the system or server.
     * This method checks both the environment variables and the server variables.
     */
    public static function getEnvVar(string $envVarName): ?string
    {
        // Verificar no ambiente atual
        $value = getenv($envVarName) ?: $_SERVER[$envVarName] ?? null;

        // Se não estiver no ambiente atual, buscar no arquivo de perfil do shell
        if (!$value) {
            $value = self::getEnvVarFromShell($envVarName);
        }

        return $value;
    }

    /**
     * Save an environment variable to the user's shell profile file.
     * This method appends the export command to the shell profile file.
     */
    public static function saveEnvVar(string $envVarName, string $key): bool
    {
        $shell = self::getShell();

        putenv("$envVarName=$key");

        if ($shell) {
            if (!self::isEnvVarInShellFile($envVarName, $shell)) {
                file_put_contents($shell, "export $envVarName='$key'\n", FILE_APPEND);
            }

            return true;
        }

        return false;
    }

    /**
     * Check if a file exists and is readable.
     * This method is primarily used for testing.
     */
    public static function isFileReadable(string $filename): bool
    {
        return file_exists($filename) && is_readable($filename);
    }

    /**
     * Check if the environment variable is already in the shell profile file.
     */
    public static function isEnvVarInShellFile(string $envVarName, string $shellFile): bool
    {
        if (!file_exists($shellFile)) {
            return false;
        }

        // Use the isFileReadable method to check if the file is readable
        if (!self::isFileReadable($shellFile)) {
            return false;
        }

        $content = file_get_contents($shellFile);
        if (false === $content) {
            return false;
        }

        return false !== strpos($content, "export $envVarName=");
    }

    /**
     * Get the shell profile file based on the user's environment.
     * This method checks for common shell profile files like .bash_profile, .bashrc, and .zshrc.
     * It returns the first one found or null if none are found.
     */
    public static function getShell(): ?string
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

    /**
     * Get an environment variable from the shell profile file.
     * This method reads the shell profile file and extracts the value of the specified environment variable.
     */
    public static function getEnvVarFromShell(string $envVarName): ?string
    {
        $shell = self::getShell();
        if ($shell && file_exists($shell)) {
            $content = file_get_contents($shell);
            if (false !== $content) {
                preg_match("/export $envVarName='([^']+)'/", $content, $matches);

                return $matches[1] ?? null;
            }
        }

        return null;
    }

    /**
     * Remove an environment variable from the user's shell profile file and the PHP environment.
     */
    public static function removeEnvVar(string $envVarName): bool
    {
        $shell = self::getShell();
        putenv($envVarName);

        if ($shell && file_exists($shell)) {
            $content = file_get_contents($shell);
            if (false !== $content) {
                // Remove the line containing the environment variable
                $updatedContent = preg_replace("/^export $envVarName='[^']*'\\n?/m", '', $content);
                if (null !== $updatedContent) {
                    file_put_contents($shell, $updatedContent);
                }
            }
        }

        return true;
    }
}
