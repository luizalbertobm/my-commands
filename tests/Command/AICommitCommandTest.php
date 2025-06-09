<?php

namespace MyCommands\Tests\Command;

use MyCommands\Command\AICommitCommand;
use PHPUnit\Framework\TestCase;

class AICommitCommandTest extends TestCase
{
    private function callCleanMessage(string $message): string
    {
        $reflection = new \ReflectionClass(AICommitCommand::class);
        $method = $reflection->getMethod('cleanMessage');
        $method->setAccessible(true);
        $instance = $reflection->newInstance();

        return $method->invoke($instance, $message);
    }

    public function testCleanMessageRemovesCodeBlock(): void
    {
        $input = "```php\n<?php echo 'test'; ?>\n```";
        $expected = "<?php echo 'test'; ?";
        $this->assertSame($expected, $this->callCleanMessage($input));
    }

    public function testCleanMessageTrimsSpecialChars(): void
    {
        $input = "***Fix***";
        $this->assertSame('Fix', $this->callCleanMessage($input));
    }

    public function testCleanMessageHandlesEmptyString(): void
    {
        $this->assertSame('', $this->callCleanMessage(''));
    }
}
