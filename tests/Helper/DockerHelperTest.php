<?php

namespace MyCommands\Tests\Helper;

use MyCommands\Helper\DockerHelper;
use PHPUnit\Framework\TestCase;

class DockerHelperTest extends TestCase
{
    private string $tmpDir;
    private string $oldPath;

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . '/docker_stub_' . uniqid();
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
        $path = $this->tmpDir . '/docker';
        file_put_contents($path, $script);
        chmod($path, 0755);
        $this->oldPath = getenv('PATH');
        putenv('PATH=' . $this->tmpDir . ':' . $this->oldPath);
    }

    protected function tearDown(): void
    {
        putenv('PATH=' . $this->oldPath);
        @unlink($this->tmpDir . '/docker');
        @rmdir($this->tmpDir);
    }

    public function testGetContainerRowsAndIds(): void
    {
        $helper = new DockerHelper();
        $rows = $helper->getContainerRows();
        $ids = $helper->getContainerIds();
        $stopped = $helper->stopContainers($ids);

        $expectedRows = [
            [1, '123', 'test_container', 'image:test', '0.0.0.0:80->80/tcp'],
            [2, '456', 'other_container', 'image:other', '-'],
        ];
        $this->assertSame($expectedRows, $rows);
        $this->assertSame(['123', '456'], $ids);
        $this->assertSame(['123', '456'], $stopped);
    }

    public function testGetContainerRowsEmpty(): void
    {
        putenv('DOCKER_TEST_EMPTY=1');
        $_ENV['DOCKER_TEST_EMPTY'] = '1';
        $helper = new DockerHelper();
        $this->assertSame([], $helper->getContainerRows());
        $this->assertSame([], $helper->getContainerIds());
        $this->assertSame([], $helper->stopContainers([]));
        putenv('DOCKER_TEST_EMPTY');
        unset($_ENV['DOCKER_TEST_EMPTY']);
    }
}
