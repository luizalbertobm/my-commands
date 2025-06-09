<?php

namespace MyCommands\Tests\Helper;

use MyCommands\Helper\GitHelper;
use MyCommands\Message;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class GitHelperTest extends TestCase
{
    public function testIsGitAvailable()
    {
        $result = GitHelper::isGitAvailable();
        $this->assertTrue($result, 'Git should be available on the system.');
    }

    public function testGetDiffReturnsString()
    {
        $diff = GitHelper::getDiff();
        // In this case we only need to check that the diff is not null
        // since we know the return type is always string from the method signature
        $this->assertNotNull($diff, 'The diff should not be null');
    }

    public function testCommitAndPushThrowsExceptionOnFailure()
    {
        $this->expectException(ProcessFailedException::class);

        // Simulate a failure by providing an invalid commit message
        GitHelper::commitAndPush('');
    }

    public function _testBuildCommitPromptThrowsExceptionWhenNoChanges()
    {
        // stash the changes first
        GitHelper::stashChanges('Stashing changes for test.');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(Message::NO_CHANGES->value);

        // Simulate no changes in the repository
        GitHelper::buildCommitPrompt();
    }

    public function testBuildCommitPromptReturnsString()
    {
        // create a file in the project root to simulate changes
        $filePath = __DIR__.'/test_file.txt';
        file_put_contents($filePath, 'Temporary change');
        // add the file to git
        GitHelper::stageChanges($filePath);

        $prompt = GitHelper::buildCommitPrompt();
        // Check if it's not null and not empty
        $this->assertNotNull($prompt, 'The commit prompt should not be null');
        $this->assertNotEmpty($prompt, 'The commit prompt should not be empty');

        // Clean up the file
        GitHelper::unstageChanges($filePath);
        unlink($filePath);
    }

    public function testDropStashRemovesCreatedStash(): void
    {
        if (!GitHelper::isGitAvailable()) {
            $this->markTestSkipped('Git is not available.');
        }

        $originalCwd = getcwd();
        $tempDir = sys_get_temp_dir().'/git_test_'.uniqid();
        mkdir($tempDir);

        chdir($tempDir);
        exec('git init');
        file_put_contents('file.txt', 'initial');
        exec('git add file.txt');
        exec('git commit -m "init"');

        file_put_contents('file.txt', 'changed');
        exec('git add file.txt');
        exec('git stash');

        $process = new Process(['git', 'stash', 'list']);
        $process->run();
        $stashesBefore = trim($process->getOutput());
        $this->assertNotEmpty($stashesBefore);

        GitHelper::dropStash(0);

        $process = new Process(['git', 'stash', 'list']);
        $process->run();
        $stashesAfter = trim($process->getOutput());
        $this->assertEmpty($stashesAfter);

        chdir($originalCwd);
        exec('rm -rf '.escapeshellarg($tempDir));
    }
}
