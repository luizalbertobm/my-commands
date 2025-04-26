<?php

namespace MyCommands\Tests\Helper;

use MyCommands\Helper\GitHelper;
use MyCommands\Message;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Exception\ProcessFailedException;

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
        $this->assertIsString($diff, 'The diff should be a string.');
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
        $filePath = __DIR__ . '/test_file.txt';
        file_put_contents($filePath, 'Temporary change');
        // add the file to git
        GitHelper::stageChanges($filePath);

        $prompt = GitHelper::buildCommitPrompt();
        $this->assertIsString($prompt, 'The commit prompt should be a string.');

        // Clean up the file
        GitHelper::unstageChanges($filePath);
        unlink($filePath);
    }
}