<?php

namespace MyCommands\Tests\DTO;

use MyCommands\DTO\OpenAIPayload;
use MyCommands\Message;
use PHPUnit\Framework\TestCase;

class OpenAIPayloadTest extends TestCase
{
    public function testToArrayForCommit(): void
    {
        $payload = new OpenAIPayload('msg', isCommit: true);
        $data = $payload->toArray();
        $this->assertSame(Message::SYSTEM_ROLE_COMMIT->value, $data['messages'][0]['content']);
        $this->assertSame('msg', $data['messages'][1]['content']);
    }

    public function testToArrayForAsk(): void
    {
        $payload = new OpenAIPayload('hello', isCommit: false);
        $data = $payload->toArray();
        $this->assertSame(Message::SYSTEM_ROLE_ASK->value, $data['messages'][0]['content']);
        $this->assertSame('hello', $data['messages'][1]['content']);
    }
}
