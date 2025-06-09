<?php

namespace MyCommands\Tests\Command;

use MyCommands\Command\EnvUnsetCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class EnvUnsetCommandTest extends TestCase
{
    private string $tempDir;
    private string $shellFile;
    private string $originalHome;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir().'/env_unset_'.uniqid();
        mkdir($this->tempDir, 0777, true);
        $this->shellFile = $this->tempDir.'/.bashrc';
        file_put_contents($this->shellFile, "export FOO='bar'\n");
        $this->originalHome = getenv('HOME') ?: '';
        putenv('HOME='.$this->tempDir);
        putenv('FOO=bar');
    }

    protected function tearDown(): void
    {
        putenv('HOME='.$this->originalHome);
        putenv('FOO');
        @unlink($this->shellFile);
        @rmdir($this->tempDir);
    }

    public function testExecuteSuccess(): void
    {
        $tester = new CommandTester(new EnvUnsetCommand());
        $tester->setInputs(['FOO']);
        $status = $tester->execute([]);

        $this->assertSame(Command::SUCCESS, $status);
        $this->assertStringContainsString('successfully unset', $tester->getDisplay());
        $this->assertStringNotContainsString('FOO', file_get_contents($this->shellFile));
        $this->assertFalse(getenv('FOO'));
    }

    public function testExecuteFailsOnEmptyName(): void
    {
        $tester = new CommandTester(new EnvUnsetCommand());
        $tester->setInputs(['']);
        $status = $tester->execute([]);

        $this->assertSame(Command::FAILURE, $status);
        $this->assertStringContainsString('name cannot be empty', $tester->getDisplay());
    }
}
