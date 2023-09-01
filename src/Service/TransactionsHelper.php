<?php


namespace App\Service;


class TransactionsHelper
{
    /**
     * @param string $countryCode
     * @return bool
     */
    public function isEuCountry(string $countryCode): bool
    {
        $euCountries = [
            'AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI',
            'FR', 'GR', 'HR', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT',
            'NL', 'PO', 'PT', 'RO', 'SE', 'SI', 'SK'
        ];

        return in_array($countryCode, $euCountries);
    }
}