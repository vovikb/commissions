<?php

namespace App\Command;

use App\Service\BinProviderInterface;
use App\Service\TransactionsHelper;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Service\ExchangeRatesProviderInterface;

class CommissionsCalculatorCommand extends Command
{
    private ExchangeRatesProviderInterface $ratesProvider;
    private BinProviderInterface $binProvider;
    private TransactionsHelper $transactionsHelper;

    protected static $defaultName = 'app:calc-commissions';
    protected static $defaultDescription = 'Calculate commissions according to the transactions input file';


    public function __construct(
        ExchangeRatesProviderInterface $ratesProvider,
        BinProviderInterface $binProvider,
        TransactionsHelper $transactionsHelper
    )
    {
        parent::__construct();
        $this->ratesProvider = $ratesProvider;
        $this->binProvider = $binProvider;
        $this->transactionsHelper = $transactionsHelper;
    }

    protected function configure(): void
    {
        $this->addArgument('input_file', InputArgument::REQUIRED, 'Transactions text file path');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $inputFile = $input->getArgument('input_file');

        $fileContents = file($inputFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        $binCountries = [];

        foreach ($fileContents as $row) {
            $transactionValues = json_decode($row, true);

            $bin = $transactionValues['bin'];
            if (!isset($binCountries[$bin])) {
                $binCountries[$bin] = $this->binProvider->getCountryByBin($bin);
            }

            $isEu = $this->transactionsHelper->isEuCountry($binCountries[$bin]);

            $currencyTicker = $transactionValues['currency'];
            $amount = $transactionValues['amount'];

            $rate = $this->ratesProvider->getExchangeRateByCurrencyTicker($currencyTicker);

            $amountFixed = $currencyTicker === 'EUR' || $rate == 0 ? $amount : $amount / $rate;

            $commission = $amountFixed * ($isEu ? 0.01 : 0.02);

            $commission = ceil($commission * 100) / 100;

            $output->writeln($commission);
        }

        return Command::SUCCESS;
    }
}
