<?php

namespace MyCommands\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'currency:convert',
    description: 'Convert currency using an external API.',
)]
class CurrencyConvertCommand extends Command
{

    private HttpClientInterface $httpClient;

    public function __construct()
    {
        parent::__construct();
        $this->httpClient = HttpClient::create();
    }

    public function setHttpClient(HttpClientInterface $httpClient): void
    {
        $this->httpClient = $httpClient;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Converts an amount from one currency to another.')
            ->addOption('amount', null, InputOption::VALUE_OPTIONAL, 'The amount to convert')
            ->addOption('from', null, InputOption::VALUE_OPTIONAL, 'The source currency (e.g., USD)')
            ->addOption('to', null, InputOption::VALUE_OPTIONAL, 'The target currency (e.g., EUR)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Verificar se as opções foram fornecidas
        $amount = $input->getOption('amount');
        $from = $input->getOption('from');
        $to = $input->getOption('to');

        // Se as opções não forem fornecidas, solicitar interativamente
        if (!$amount) {
            $amount = $io->ask('Enter the amount to convert', null, function ($value) {
                if (!is_numeric($value) || $value <= 0) {
                    throw new \RuntimeException('The amount must be a positive number.');
                }
                return $value;
            });
        }

        if (!$from) {
            $from = $io->ask('Enter the source currency (e.g., USD)', null, function ($value) {
                if (strlen($value) !== 3) {
                    throw new \RuntimeException('The source currency must be a 3-letter code.');
                }
                return strtoupper($value);
            });
        }

        if (!$to) {
            $to = $io->ask('Enter the target currency (e.g., EUR)', null, function ($value) {
                if (strlen($value) !== 3) {
                    throw new \RuntimeException('The target currency must be a 3-letter code.');
                }
                return strtoupper($value);
            });
        }

        $url = sprintf(
            'https://cdn.jsdelivr.net/npm/@fawazahmed0/currency-api@latest/v1/currencies/%s.json',
            strtolower($from)
        );

        try {
            $response = $this->httpClient->request('GET', $url);
            $data = $response->toArray();

            if (!isset($data[strtolower($from)][strtolower($to)])) {
                $io->error('Invalid target currency.');
                return Command::FAILURE;
            }

            $rate = $data[strtolower($from)][strtolower($to)];
            $convertedAmount = $amount * $rate;

            $io->success(sprintf('%s %s is equal to %.2f %s', $amount, strtoupper($from), $convertedAmount, strtoupper($to)));
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Failed to fetch conversion rate: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}