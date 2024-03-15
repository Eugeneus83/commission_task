<?php

namespace Eugene\CommissionTask\Service;

use Eugene\CommissionTask\Exceptions\RateNotExtractedException;
use Eugene\CommissionTask\Exceptions\RateNotFoundException;

class CurrencyRateManager
{
    /* @var array */

    private $rates = [];

    /* @var string */

    private $baseCurrencyCode;

    /* @var string */

    private $feedUrlBase = 'https://www.floatrates.com/daily/';

    function __construct($baseCurrencyCode)
    {
        $this->baseCurrencyCode = $baseCurrencyCode;
        $this->updateRates($this->baseCurrencyCode);
    }

    /* @return float */

    public function getRate(string $currencyCode): float
    {
        if ($currencyCode === $this->baseCurrencyCode) {
            return 1;
        }
        foreach ($this->rates as $rateCurrencyCode => $rate) {
            if ($currencyCode === $rateCurrencyCode) {
                return $rate;
            }
        }
        throw new RateNotFoundException($currencyCode);
    }

    public function updateRates($currencyCode): void
    {
        $client = new \GuzzleHttp\Client(['base_uri' => $this->feedUrlBase. '/'. strtolower($currencyCode). '.json']);
        $response = $client->request('GET');
        if ($response->getStatusCode() !== 200) {
            throw new RateNotExtractedException();
        }
        $response = $response->getBody()->getContents();
        $json = json_decode($response, true);
        if ($json === null) {
            throw new RateNotExtractedException();
        }

        foreach ($json as $row) {
            $this->rates[$row['code']] = $row['rate'];
        }
    }

}