<?php

namespace MyCommands\Tests\Command;

use MyCommands\Command\DockerListCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class DockerListCommandTest extends TestCase
{
    private string $tmpDir;
    private string $oldPath;

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir().'/docker_stub_'.uniqid();
        mkdir($this->tmpDir);
        $script = <<<'SH'
#!/bin/sh
if [ "$1" = "ps" ] && [ "$2" = "--format" ]; then
    if [ "$DOCKER_TEST_EMPTY" = "1" ]; then
        exit 0
    fi
    echo "123|test_container|image:test|0.0.0.0:80->80/tcp"
    echo "456|other_container|image:other|"
    exit 0
fi
if [ "$1" = "ps" ] && [ "$2" = "-q" ]; then
    if [ "$DOCKER_TEST_EMPTY" = "1" ]; then
        exit 0
    fi
    echo "123"
    echo "456"
    exit 0
fi
if [ "$1" = "stop" ]; then
    shift
    for id in "$@"; do
        echo "$id"
    done
    exit 0
fi
exit 0
SH;
        $path = $this->tmpDir.'/docker';
        file_put_contents($path, $script);
        chmod($path, 0755);
        $this->oldPath = getenv('PATH');
        putenv('PATH='.$this->tmpDir.':'.$this->oldPath);
    }

    protected function tearDown(): void
    {
        putenv('PATH='.$this->oldPath);
        @unlink($this->tmpDir.'/docker');
        @rmdir($this->tmpDir);
    }

    public function testExecuteListsContainers(): void
    {
        $tester = new CommandTester(new DockerListCommand());
        $status = $tester->execute([]);

        $this->assertSame(Command::SUCCESS, $status);
        $display = $tester->getDisplay();
        $this->assertStringContainsString('Running containers:', $display);
        $this->assertStringContainsString('test_container', $display);
        $this->assertStringContainsString('other_container', $display);
    }

    public function testExecuteNoContainers(): void
    {
        putenv('DOCKER_TEST_EMPTY=1');
        $_ENV['DOCKER_TEST_EMPTY'] = '1';

        $tester = new CommandTester(new DockerListCommand());
        $status = $tester->execute([]);

        $this->assertSame(Command::SUCCESS, $status);
        $this->assertStringContainsString('No running Docker containers.', $tester->getDisplay());

        putenv('DOCKER_TEST_EMPTY');
        unset($_ENV['DOCKER_TEST_EMPTY']);
    }
}
