<?php

use App\Service\JsonBinProvider;
use App\Service\JsonExchangeRatesProvider;
use App\Service\HttpHelper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use App\Command\CommissionsCalculatorCommand;
use App\Service\TransactionsHelper;


class CommissionsCalculatorCommandTest extends TestCase
{
    public function testExecute()
    {
        $httpHelper = $this->createMock(HttpHelper::class);
        $baseUrl = 'https://example.com/api/';
        $apiKey = 'your_api_key';
        $ratesPath = $countryCodePath = 'somepath';

        $ratesProvider = new JsonExchangeRatesProvider($baseUrl, $apiKey, $ratesPath, $httpHelper);

        $rates = [
            'EUR' => 1,
            'USD' => 1.078225,
            'JPY' => 157.693658,
            'GBP' => 0.856672,
        ];
        $ratesProvider->setExchangeRates($rates);

        $binProvider = new JsonBinProvider($baseUrl, $countryCodePath, $httpHelper);

        $binCountries = [
            '45717360' => 'DK',
            '516793' => 'LT',
            '45417360' => 'JP',
            '41417360' => 'US',
            '4745030' => 'GB',
        ];
        foreach ($binCountries as $bin => $countryCode) {
            $binProvider->addBinCountry($bin, $countryCode);
        }

        $command = new CommissionsCalculatorCommand($ratesProvider, $binProvider, new TransactionsHelper());

        $application = new Application();
        $application->add($command);

        $commandTester = new CommandTester($command);

        $input = [
            'input_file' => 'var/transactions/2023_09_01.txt',
        ];

        $commandTester->execute($input);

        $output = $commandTester->getDisplay();

        $expectedOutput = [
            1.0,
            0.47,
            1.27,
            2.42,
            46.7,
        ];

        $actualOutput = explode("\n", trim($output));

        foreach ($expectedOutput as $key => $expectedValue) {
            $this->assertEquals($expectedValue, (float) trim($actualOutput[$key]));
        }

    }
}