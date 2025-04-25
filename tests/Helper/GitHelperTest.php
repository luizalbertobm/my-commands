<?php

namespace MyCommands\Tests\Helper;

use MyCommands\Helper\GitHelper;
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

    public function testBuildCommitPromptThrowsExceptionWhenNoChanges()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No changes to commit.');

        // Simulate no changes in the repository
        GitHelper::buildCommitPrompt();
    }

    public function testBuildCommitPromptReturnsString()
    {
        // Simulate staged changes in the repository
        $prompt = GitHelper::buildCommitPrompt();
        $this->assertIsString($prompt, 'The commit prompt should be a string.');
    }
}