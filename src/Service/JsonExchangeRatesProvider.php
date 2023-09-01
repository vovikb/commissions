<?php


namespace App\Service;

use Exception;

class JsonExchangeRatesProvider implements ExchangeRatesProviderInterface
{
    private HttpHelper $httpHelper;

    private string $baseUrl;
    private string $apiKey;
    private string $ratesPath;
    private array $rates = [];

    public function __construct(
        $baseUrl,
        $apiKey,
        $ratesPath,
        HttpHelper $httpHelper
    )
    {
        $this->baseUrl = $baseUrl;
        $this->apiKey = $apiKey;
        $this->ratesPath = $ratesPath;
        $this->httpHelper = $httpHelper;
    }

    public function setExchangeRates(array $exchangeRates): void
    {
        $this->rates = $exchangeRates;
    }

    public function getExchangeRates(): array
    {
        if (!$this->rates) {

            $endpoint = 'exchangerates_data/latest';
            $headers['apikey'] = $this->apiKey;
            $ratesRes = $this->httpHelper->sendRequest('GET', $this->baseUrl, $endpoint, $headers);

            if (null === $ratesRes || isset($ratesRes['error'])) {
                throw new Exception('Error getting exchange rates');
            }

            $ratesData = json_decode($ratesRes, true);

            $ratesPathArr = explode('.', $this->ratesPath);

            $rates = $ratesData;
            foreach ($ratesPathArr as $key) {
                $rates = $rates[$key];
            }

            $this->setExchangeRates($rates);
        }

        return $this->rates;
    }

    public function getExchangeRateByCurrencyTicker(string $ticker): float
    {
        $rates = $this->getExchangeRates();

        return $rates[$ticker] ?? 0;
    }
}