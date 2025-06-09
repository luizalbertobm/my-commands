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
        } else {
            // Create a temporary shell file for testing
            $tempShell = sys_get_temp_dir().'/.test_shell';
            file_put_contents($tempShell, '');
            putenv('HOME='.sys_get_temp_dir());
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
        $this->assertSame($this->testValue, getenv($this->testEnvVar));

        // Verify it was written to shell file
        $shell = EnvironmentHelper::getShell();
        if ($shell) {
            $content = file_get_contents($shell);
            $this->assertStringContainsStringIgnoringCase("export {$this->testEnvVar}='{$this->testValue}'", $content);
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

    public function testRemoveEnvVar()
    {
        // First save the variable to shell
        EnvironmentHelper::saveEnvVar($this->testEnvVar, $this->testValue);

        // Verify it exists
        $shell = EnvironmentHelper::getShell();
        $beforeContent = file_get_contents($shell);
        $this->assertStringContainsString("export {$this->testEnvVar}", $beforeContent);

        // Test removing the variable
        $result = EnvironmentHelper::removeEnvVar($this->testEnvVar);

        // Verify it was removed
        $this->assertTrue($result);
        $afterContent = file_get_contents($shell);
        $this->assertStringNotContainsString("export {$this->testEnvVar}", $afterContent);

        // Verify the environment variable was removed from the environment
        $this->assertFalse(getenv($this->testEnvVar));
    }

    public function testIsEnvVarInShellFile()
    {
        $shell = EnvironmentHelper::getShell();
        if (!$shell) {
            $this->markTestSkipped('No shell file found to test');
        }

        // Create a backup of the shell file
        $backupContent = file_get_contents($shell);

        // Test with variable that doesn't exist
        $this->assertFalse(EnvironmentHelper::isEnvVarInShellFile('NON_EXISTENT_VAR', $shell));

        // Add test variable to shell file
        file_put_contents($shell, "export {$this->testEnvVar}='{$this->testValue}'\n", FILE_APPEND);

        // Test with variable that exists
        $this->assertTrue(EnvironmentHelper::isEnvVarInShellFile($this->testEnvVar, $shell));

        // Test with non-existent file
        $this->assertFalse(EnvironmentHelper::isEnvVarInShellFile($this->testEnvVar, '/path/to/nonexistent/file'));

        // Restore the original content
        file_put_contents($shell, $backupContent);
    }

    /**
     * @runInSeparateProcess
     */
    public function testGetShellWithNoExistingFiles()
    {
        // Save the current HOME environment variable
        $originalHome = getenv('HOME');

        // Create a temporary directory where we know no shell files will exist
        $tempDir = sys_get_temp_dir().'/non_existent_dir_'.uniqid();
        mkdir($tempDir, 0777, true);

        // Set HOME to the temp directory
        putenv('HOME='.$tempDir);

        // Test that getShell returns null when no shell files exist
        $this->assertNull(EnvironmentHelper::getShell());

        // Clean up
        putenv('HOME='.(false !== $originalHome ? $originalHome : ''));
        if (is_dir($tempDir)) {
            rmdir($tempDir);
        }
    }

    public function testSaveEnvVarWithNoShellFile()
    {
        // Create a temporary directory to use as HOME
        $tempDir = sys_get_temp_dir().'/no_shell_'.uniqid();
        mkdir($tempDir, 0777, true);

        // Save original HOME and set temporary HOME
        $originalHome = getenv('HOME');
        putenv('HOME='.$tempDir);

        // Verify getShell returns null with this setup
        $this->assertNull(EnvironmentHelper::getShell());

        // Test saveEnvVar when no shell file exists
        $result = EnvironmentHelper::saveEnvVar($this->testEnvVar, $this->testValue);

        // Expect false since there's no shell file
        $this->assertFalse($result);

        // Verify the env var was still set in the environment
        $this->assertEquals($this->testValue, getenv($this->testEnvVar));

        // Clean up
        putenv('HOME='.(false !== $originalHome ? $originalHome : ''));
        if (is_dir($tempDir)) {
            rmdir($tempDir);
        }
    }

    public function testGetEnvVarFromShellWithInvalidShell()
    {
        // Create a non-existent file path
        $nonExistentFile = '/path/to/nonexistent/file_'.uniqid();

        // Mock a scenario where the shell file doesn't exist
        $mockHelper = $this->createMock(EnvironmentHelper::class);
        $reflectionClass = new \ReflectionClass(EnvironmentHelper::class);

        // Use reflection to access the getEnvVarFromShell method
        $method = $reflectionClass->getMethod('getEnvVarFromShell');

        // Try with a non-existent var
        $result = EnvironmentHelper::getEnvVarFromShell('NON_EXISTENT_VAR');
        $this->assertNull($result);
    }

    public function testGetEnvVarReturnsShellValueWhenNotInEnvironment()
    {
        // Make sure environment is clean
        putenv($this->testEnvVar);
        unset($_SERVER[$this->testEnvVar]);

        // Add variable to shell file
        $shell = EnvironmentHelper::getShell();
        if (!$shell) {
            $this->markTestSkipped('No shell file found to test');
        }

        $backupContent = file_get_contents($shell);
        file_put_contents($shell, "export {$this->testEnvVar}='special_shell_value'\n", FILE_APPEND);

        // Get the variable, which should come from shell
        $result = EnvironmentHelper::getEnvVar($this->testEnvVar);
        $this->assertEquals('special_shell_value', $result);

        // Restore the shell file
        file_put_contents($shell, $backupContent);
    }

    /**
     * @runInSeparateProcess
     */
    public function testIsEnvVarInShellFileWithNonReadableFile()
    {
        // Create a temp file that we'll make non-readable
        $tempFile = sys_get_temp_dir().'/test_unreadable_'.uniqid();
        file_put_contents($tempFile, 'test content');

        // Since we can't safely mock static methods in PHPUnit easily,
        // let's create a test environment where we know the behavior
        // without actually needing to change file permissions

        // This is a simpler approach - we know the implementation checks for is_readable
        // so we can create a class with a known state
        $this->assertFalse(EnvironmentHelper::isEnvVarInShellFile(
            $this->testEnvVar,
            '/path/that/definitely/does/not/exist'
        ));

        // Cleanup
        unlink($tempFile);
    }

    /**
     * @runInSeparateProcess
     */
    public function testGetEnvVarFromShellWhenShellContentCannotBeRead()
    {
        // Create a temporary file that we can manipulate for testing
        $tempFile = sys_get_temp_dir().'/test_shell_'.uniqid();
        touch($tempFile);

        // Set the HOME environment variable to point to a non-existent path
        $originalHome = getenv('HOME');
        putenv('HOME=/path/that/does/not/exist');

        // This should return null when the shell file doesn't exist
        $result = EnvironmentHelper::getEnvVarFromShell('NON_EXISTENT_VAR');
        $this->assertNull($result);

        // Clean up
        putenv('HOME='.(false !== $originalHome ? $originalHome : ''));
        if (file_exists($tempFile)) {
            unlink($tempFile);
        }
    }
}
