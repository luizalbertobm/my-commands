<?php

namespace MyCommands\Tests;

use MyCommands\Language;
use MyCommands\Message;
use PHPUnit\Framework\TestCase;

class LanguageMessageTest extends TestCase
{
    public function testGetAllLanguagesReturnsEnumValues(): void
    {
        $expected = [
            'English',
            'Portuguese',
            'French',
            'Spanish',
        ];
        $this->assertSame($expected, Language::getAllLanguages());
    }

    public function testMessageFormatInterpolatesValues(): void
    {
        $msg = Message::API_ERROR->format('something');
        $this->assertSame('OpenAI API error: something', $msg);
    }
}
