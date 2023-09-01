<?php

namespace App\Service;

interface BinProviderInterface
{
    public function getBinData(string $bin): array;

    public function getCountryByBin(string $bin): string;

    public function addBinCountry(int $bin, string $countryCode): void;
}