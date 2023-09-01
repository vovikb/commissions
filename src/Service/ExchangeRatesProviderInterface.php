<?php

namespace App\Service;

interface ExchangeRatesProviderInterface
{
    public function setExchangeRates(array $exchangeRates): void;

    public function getExchangeRates(): array;

    public function getExchangeRateByCurrencyTicker(string $ticker): float;
}