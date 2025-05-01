<?php

namespace MyCommands\Helper;

use ZipArchive;

class ZipHelper
{
    /**
     * Compresses a directory into a zip file.
     *
     * @param  string            $sourceDir Path to the directory to zip
     * @param  string            $zipPath   Path where the zip file will be created
     * @throws \RuntimeException If zip creation fails
     */
    public function zipDirectory(string $sourceDir, string $zipPath): void
    {
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
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
            $filePath = $file->getRealPath();
            if ($filePath === $zipPath) {
                continue;
            }
            $relativePath = substr($filePath, strlen($sourceDir) + 1);
            $zip->addFile($filePath, $relativePath);
        }

        $zip->close();
    }
}
