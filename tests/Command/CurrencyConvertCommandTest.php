<?php

namespace MyCommands\Tests\Command;

use MyCommands\Command\CurrencyConvertCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class CurrencyConvertCommandTest extends TestCase
{
    public function testSuccessfulConversion(): void
    {
        $mockResponse = new MockResponse(json_encode(['usd' => ['eur' => 0.85]]));
        $mockClient = new MockHttpClient($mockResponse);

        $command = new CurrencyConvertCommand();
        $command->setHttpClient($mockClient);

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            '--amount' => 100,
            '--from' => 'USD',
            '--to' => 'EUR',
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('100 USD is equal to 85.00 EUR', $output);
    }

    public function testMissingOptions(): void
    {
        // Mockamos a interação do usuário usando setInputs
        $command = new CurrencyConvertCommand();
        $commandTester = new CommandTester($command);

        // Simulamos as respostas interativas para amount, from e to
        $commandTester->setInputs(['100', 'USD', 'EUR']);
        $commandTester->execute([]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Enter the amount to convert', $output);
        $this->assertStringContainsString('Enter the source currency', $output);
        $this->assertStringContainsString('Enter the target currency', $output);
    }

    public function testInvalidTargetCurrency(): void
    {
        // Mockamos uma resposta onde a moeda alvo não está presente
        $mockResponse = new MockResponse(json_encode(['usd' => ['eur' => 0.85]]));
        $mockClient = new MockHttpClient($mockResponse);

        $command = new CurrencyConvertCommand();
        $command->setHttpClient($mockClient);

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            '--amount' => 100,
            '--from' => 'USD',
            '--to' => 'INVALID', // Uma moeda que não está na resposta
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Invalid target currency.', $output);
    }

    public function testApiFailure(): void
    {
        $mockResponse = new MockResponse('Internal Server Error', [
            'http_code' => 500,
        ]);
        $mockClient = new MockHttpClient($mockResponse);

        $command = new CurrencyConvertCommand();
        $command->setHttpClient($mockClient);

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            '--amount' => 100,
            '--from' => 'USD',
            '--to' => 'EUR',
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Failed to fetch conversion rate', $output);
        $this->assertSame(Command::FAILURE, $commandTester->getStatusCode());
    }
}
