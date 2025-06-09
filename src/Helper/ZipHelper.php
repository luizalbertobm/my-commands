<?php

namespace MyCommands\Helper;

class ZipHelper
{
    /**
     * Compresses a directory into a zip file.
     *
     * @param string $sourceDir Path to the directory to zip
     * @param string $zipPath   Path where the zip file will be created
     *
     * @throws \RuntimeException If zip creation fails
     */
    public function zipDirectory(string $sourceDir, string $zipPath): void
    {
        $zip = new \ZipArchive();
        if (true !== $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE)) {
            throw new \RuntimeException("Failed to create zip file at $zipPath");
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sourceDir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if (!$file->isFile()) {
                continue;
            }
            $filePath = $file->getPathname();
            if ($filePath === $zipPath) {
                continue;
            }
            $relativePath = substr($filePath, strlen($sourceDir) + 1);

            // For virtual file systems, we need to read the content and add it as a string
            if (0 === strpos($filePath, 'vfs://')) {
                $content = file_get_contents($filePath);
                if (false === $content) {
                    $content = '';
                }
                $zip->addFromString($relativePath, $content);
            } else {
                $zip->addFile($filePath, $relativePath);
            }
        }

        $zip->close();
    }
}
