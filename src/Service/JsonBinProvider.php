<?php


namespace App\Service;

use Exception;

class JsonBinProvider implements BinProviderInterface
{
    private HttpHelper $httpHelper;

    private string $baseUrl;
    private string $countryCodePath;
    private array $binCountries = [];

    public function __construct(
        $baseUrl,
        $countryCodePath,
        HttpHelper $httpHelper
    )
    {
        $this->baseUrl = $baseUrl;
        $this->countryCodePath = $countryCodePath;
        $this->httpHelper = $httpHelper;
    }

    public function getBinData(string $bin): array
    {
        $endpoint = $bin;

        $binData = $this->httpHelper->sendRequest('GET', $this->baseUrl, $endpoint);

        if (null === $binData) {
            throw new Exception('Error getting bin data');
        }

        return json_decode($binData, true);
    }

    public function getCountryByBin(string $bin): string
    {
        if (!isset($this->binCountries[$bin])) {
            $binData = $this->getBinData($bin);

            $countryCodePathArr = explode('.', $this->countryCodePath);

            $res = $binData;
            foreach ($countryCodePathArr as $key) {
                $res = $res[$key];
            }

            $this->addBinCountry($bin, $res);
        }

        return $this->binCountries[$bin];
    }

    public function addBinCountry(int $bin, string $countryCode): void
    {
        $this->binCountries[$bin] = $countryCode;
    }
}