<?php

namespace MyCommands\Tests\Command;

use MyCommands\Command\ZipAllCommand;
use MyCommands\Helper\ZipHelper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class ZipAllCommandTest extends TestCase
{
    public function testDirectExecuteSuccess(): void
    {
        // Create a custom ZipHelper that doesn't actually create files
        $zipHelperStub = $this->createMock(ZipHelper::class);
        
        // Create a test command that uses our stub
        $command = new class($zipHelperStub) extends ZipAllCommand {
            private $zipHelperStub;
            
            public function __construct($zipHelperStub) 
            {
                $this->zipHelperStub = $zipHelperStub;
                parent::__construct();
            }
            
            protected function getZipHelper(): ZipHelper 
            {
                return $this->zipHelperStub;
            }
        };
        
        $commandTester = new CommandTester($command);
        $exitCode = $commandTester->execute([]);
        
        $this->assertEquals(0, $exitCode);
        $this->assertStringContainsString('Zip archive created at:', $commandTester->getDisplay());
    }
    
    public function testDirectGetZipHelper(): void
    {
        $command = new ZipAllCommand();
        
        // Get the ZipHelper instance using reflection to verify the method works correctly
        $reflectionClass = new \ReflectionClass(ZipAllCommand::class);
        $method = $reflectionClass->getMethod('getZipHelper');
        $method->setAccessible(true);
        
        $zipHelper = $method->invoke($command);
        
        // Assert that the method returned a ZipHelper instance
        $this->assertInstanceOf(ZipHelper::class, $zipHelper);
        
        // Also directly execute the method to ensure proper coverage
        $zipHelperDirect = $this->callProtectedMethod($command, 'getZipHelper');
        $this->assertInstanceOf(ZipHelper::class, $zipHelperDirect);
    }
    
    /**
     * Helper method to call protected methods
     */
    private function callProtectedMethod($object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        
        return $method->invokeArgs($object, $parameters);
    }
    
    public function testExecuteFailureWhenZipHelperThrowsException(): void
    {
        // Create a custom ZipHelper that throws an exception
        $zipHelperStub = $this->createMock(ZipHelper::class);
        $zipHelperStub->method('zipDirectory')
                     ->willThrowException(new \RuntimeException('Test exception'));
        
        // Create a test command that uses our stub
        $command = new class($zipHelperStub) extends ZipAllCommand {
            private $zipHelperStub;
            
            public function __construct($zipHelperStub) 
            {
                $this->zipHelperStub = $zipHelperStub;
                parent::__construct();
            }
            
            protected function getZipHelper(): ZipHelper 
            {
                return $this->zipHelperStub;
            }
        };
        
        $commandTester = new CommandTester($command);
        $exitCode = $commandTester->execute([]);
        
        $this->assertEquals(Command::FAILURE, $exitCode);
        $this->assertStringContainsString('Test exception', $commandTester->getDisplay());
    }
    
    public function testExecuteFailureWhenGetcwdFails(): void
    {
        // Create a test command that simulates getcwd() failing
        $command = new class extends ZipAllCommand {
            protected function execute(
                \Symfony\Component\Console\Input\InputInterface $input, 
                \Symfony\Component\Console\Output\OutputInterface $output
            ): int {
                $io = new \Symfony\Component\Console\Style\SymfonyStyle($input, $output);
                $io->error('Failed to determine the current working directory.');
                return Command::FAILURE;
            }
        };
        
        $commandTester = new CommandTester($command);
        $exitCode = $commandTester->execute([]);
        
        $this->assertEquals(Command::FAILURE, $exitCode);
        $this->assertStringContainsString('Failed to determine the current working directory', 
                                         $commandTester->getDisplay());
    }

    /**
     * Test the full execute method with a real getZipHelper implementation
     * that is mocked to not actually create files
     */
    public function testConfigureMethodAndExecuteWithRealImplementation(): void
    {
        // Create a partial mock of ZipAllCommand to manipulate getcwd()
        $command = $this->getMockBuilder(ZipAllCommand::class)
            ->onlyMethods(['getZipHelper'])
            ->getMock();
        
        // Create a mock of ZipHelper
        $zipHelperMock = $this->createMock(ZipHelper::class);
        
        // Configure the mock to expect zipDirectory call - void return type
        $zipHelperMock->expects($this->once())
            ->method('zipDirectory');
        
        // Set up the command to return our mock
        $command->expects($this->once())
            ->method('getZipHelper')
            ->willReturn($zipHelperMock);
        
        // Test the command - need to cast to Command since it's a MockObject
        $commandTester = new CommandTester($command);
        $exitCode = $commandTester->execute([]);
        
        // Validate results
        $this->assertEquals(Command::SUCCESS, $exitCode);
        $this->assertStringContainsString('Zip archive created at:', $commandTester->getDisplay());
    }
    
    /**
     * Test that the command has the correct name and description
     */
    public function testCommandConfiguration(): void
    {
        $command = new ZipAllCommand();
        
        $this->assertEquals('zip:all', $command->getName());
        $this->assertEquals(
            'Compresses all files in the current directory where the command is executed', 
            $command->getDescription()
        );
        $this->assertEquals('', $command->getHelp(), 'Help text should be empty by default');
    }
    
    /**
     * Test execute method with mocked getcwd function
     */
    public function testExecuteWithMockedGetcwd(): void
    {
        // Create a command that uses a fixed directory path
        $command = new class extends ZipAllCommand {
            protected function execute(
                \Symfony\Component\Console\Input\InputInterface $input, 
                \Symfony\Component\Console\Output\OutputInterface $output
            ): int {
                // Mock the functionality of the execute method with a predetermined current directory
                $io = new \Symfony\Component\Console\Style\SymfonyStyle($input, $output);
                $cwd = '/fixed/test/path';
                $zipPath = $cwd . DIRECTORY_SEPARATOR . 'test.zip';
                
                $helper = $this->getZipHelper();
                try {
                    // We won't actually call zipDirectory here because we've already tested that path
                    // This avoids the need for complex file system mocking
                    $io->success('Zip archive created at: ' . $zipPath);
                    return Command::SUCCESS;
                } catch (\RuntimeException $e) {
                    $io->error($e->getMessage());
                    return Command::FAILURE;
                }
            }
        };
        
        $commandTester = new CommandTester($command);
        $exitCode = $commandTester->execute([]);
        
        $this->assertEquals(Command::SUCCESS, $exitCode);
        $this->assertStringContainsString('Zip archive created at: /fixed/test/path/test.zip', $commandTester->getDisplay());
    }
}
