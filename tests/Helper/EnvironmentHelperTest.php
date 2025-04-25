<?php

namespace MyCommands\Tests\Helper;

use MyCommands\Helper\EnvironmentHelper;
use PHPUnit\Framework\TestCase;

class EnvironmentHelperTest extends TestCase
{
    private string $testEnvVar = 'TEST_ENV_VAR';
    private string $testValue = 'test_value';
    private string $originalShellContent;

    protected function setUp(): void
    {
        // Store original shell content if it exists
        $shell = EnvironmentHelper::getShell();
        if ($shell && file_exists($shell)) {
            $this->originalShellContent = file_get_contents($shell);
        }
    }

    protected function tearDown(): void
    {
        // Restore original shell content if it existed
        if (isset($this->originalShellContent)) {
            $shell = EnvironmentHelper::getShell();
            if ($shell) {
                file_put_contents($shell, $this->originalShellContent);
            }
        }

        // Clean up test environment variable
        putenv($this->testEnvVar);
        unset($_SERVER[$this->testEnvVar]);
    }

    public function testGetEnvVarFromEnvironment()
    {
        putenv("{$this->testEnvVar}={$this->testValue}");
        
        $result = EnvironmentHelper::getEnvVar($this->testEnvVar);
        $this->assertEquals($this->testValue, $result);
    }

    public function testGetEnvVarFromServer()
    {
        $_SERVER[$this->testEnvVar] = $this->testValue;
        
        $result = EnvironmentHelper::getEnvVar($this->testEnvVar);
        $this->assertEquals($this->testValue, $result);
    }

    public function testGetEnvVarReturnsNullWhenNotFound()
    {
        $result = EnvironmentHelper::getEnvVar('NON_EXISTENT_VAR');
        $this->assertNull($result);
    }

    public function testSaveEnvVar()
    {
        $result = EnvironmentHelper::saveEnvVar($this->testEnvVar, $this->testValue);
        
        $this->assertTrue($result);
        $this->assertEquals($this->testValue, getenv($this->testEnvVar));
        $this->assertEquals($this->testValue, $_SERVER[$this->testEnvVar]);
        
        // Verify it was written to shell file
        $shell = EnvironmentHelper::getShell();
        if ($shell) {
            $content = file_get_contents($shell);
            $this->assertStringContainsString("export {$this->testEnvVar}='{$this->testValue}'", $content);
        }
    }

    public function testSaveEnvVarDoesNotDuplicateInShellFile()
    {
        // Save first time
        EnvironmentHelper::saveEnvVar($this->testEnvVar, $this->testValue);
        
        // Save second time with same value
        EnvironmentHelper::saveEnvVar($this->testEnvVar, $this->testValue);
        
        $shell = EnvironmentHelper::getShell();
        if ($shell) {
            $content = file_get_contents($shell);
            $count = substr_count($content, "export {$this->testEnvVar}='{$this->testValue}'");
            $this->assertEquals(1, $count, 'Environment variable should not be duplicated in shell file');
        }
    }

    public function testGetEnvVarFromShell()
    {
        // First save the variable to shell
        EnvironmentHelper::saveEnvVar($this->testEnvVar, $this->testValue);
        
        // Clear environment and server variables
        putenv($this->testEnvVar);
        unset($_SERVER[$this->testEnvVar]);
        
        // Test getting from shell
        $result = EnvironmentHelper::getEnvVarFromShell($this->testEnvVar);
        $this->assertEquals($this->testValue, $result);
    }

    public function testGetShellReturnsValidPath()
    {
        $shell = EnvironmentHelper::getShell();
        if ($shell) {
            $this->assertFileExists($shell);
            $this->assertStringContainsString('.', basename($shell));
        }
    }
} 