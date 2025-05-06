<?php

namespace MyCommands\Helper;

use Symfony\Component\Process\Process;

class DockerHelper
{
    public function getContainerRows(): array
    {
        $process = new Process(['docker', 'ps', '--format', '{{.ID}}|{{.Names}}|{{.Image}}|{{.Ports}}']);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException('Error listing Docker containers.');
        }

        $output = trim($process->getOutput());
        if ('' === $output) {
            return [];
        }

        $rows = [];
        $lines = explode("\n", $output);
        foreach ($lines as $index => $line) {
            [$id, $name, $image, $ports] = explode('|', $line, 4);

            if (!trim($ports)) {
                $portsDisplay = '-';
            } else {
                $parts = array_map('trim', explode(',', $ports));
                $portsDisplay = implode(', ', array_map(function ($part) {
                    return preg_replace('/\s+/', ' ', $part);
                }, $parts));
            }

            $rows[] = [
                $index + 1,
                $id,
                $name,
                $image,
                $portsDisplay,
            ];
        }

        return $rows;
    }

    public function getContainerIds(): array
    {
        $process = new Process(['docker', 'ps', '-q']);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException('Error retrieving running container IDs.');
        }

        $output = trim($process->getOutput());
        if ('' === $output) {
            return [];
        }

        return explode("\n", $output);
    }

    public function stopContainers(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $process = new Process(array_merge(['docker', 'stop'], $ids));
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException('Error stopping containers.');
        }

        $stopped = trim($process->getOutput());
        if ('' === $stopped) {
            return [];
        }

        return array_filter(explode("\n", $stopped));
    }
}
