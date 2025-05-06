<?php

namespace MyCommands\Tests\Command;

use MyCommands\Command\CurrencyConvertCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class CurrencyConvertCommandTest extends TestCase
{
    public function testSuccessfulConversion(): void
    {
        $mockResponse = new MockResponse(json_encode(['eur' => 0.85]));
        $mockClient = new MockHttpClient($mockResponse);

        $command = new CurrencyConvertCommand();
        $command->setHttpClient($mockClient);

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            '--amount' => 100,
            '--from' => 'usd',
            '--to' => 'eur',
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('100 USD is equal to 85.00 EUR', $output);
    }

    public function testMissingOptions(): void
    {
        $command = new CurrencyConvertCommand();
        $commandTester = new CommandTester($command);

        $commandTester->execute([]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('All options (amount, from, to) are required.', $output);
    }

    public function testInvalidTargetCurrency(): void
    {
        $mockResponse = new MockResponse(json_encode(['eur' => 0.85]));
        $mockClient = new MockHttpClient($mockResponse);

        $command = new CurrencyConvertCommand();
        $command->setHttpClient($mockClient);

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            '--amount' => 100,
            '--from' => 'usd',
            '--to' => 'invalid',
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Invalid target currency.', $output);
    }
}
