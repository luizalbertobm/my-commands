<?php

namespace MyCommands\Tests\Command;

use MyCommands\Command\EnvSetCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class EnvSetCommandTest extends TestCase
{
    private string $tempDir;
    private string $shellFile;
    private string $originalHome;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir().'/env_set_'.uniqid();
        mkdir($this->tempDir, 0777, true);
        $this->shellFile = $this->tempDir.'/.bashrc';
        file_put_contents($this->shellFile, '');
        $this->originalHome = getenv('HOME') ?: '';
        putenv('HOME='.$this->tempDir);
    }

    protected function tearDown(): void
    {
        putenv('HOME='.$this->originalHome);
        @unlink($this->shellFile);
        @rmdir($this->tempDir);
    }

    public function testExecuteSuccess(): void
    {
        $tester = new CommandTester(new EnvSetCommand());
        $tester->setInputs(['FOO', 'bar']);
        $status = $tester->execute([]);

        $this->assertSame(Command::SUCCESS, $status);
        $this->assertStringContainsString('successfully set', $tester->getDisplay());
        $this->assertStringContainsString("export FOO='bar'", file_get_contents($this->shellFile));
    }

    public function testExecuteFailsOnEmptyName(): void
    {
        $tester = new CommandTester(new EnvSetCommand());
        $tester->setInputs(['']);
        $status = $tester->execute([]);

        $this->assertSame(Command::FAILURE, $status);
        $this->assertStringContainsString('name cannot be empty', $tester->getDisplay());
    }

    public function testExecuteFailsOnEmptyValue(): void
    {
        $tester = new CommandTester(new EnvSetCommand());
        $tester->setInputs(['FOO', '']);
        $status = $tester->execute([]);

        $this->assertSame(Command::FAILURE, $status);
        $this->assertStringContainsString('value cannot be empty', $tester->getDisplay());
    }
}
