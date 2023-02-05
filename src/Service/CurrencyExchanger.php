<?php

namespace App\Service;

use Exception;
use RuntimeException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CurrencyExchanger
{
    private static array $rates = [];

    public function __construct(
        private readonly string $apiUrl,
        private readonly HttpClientInterface $client
    )
    {
        self::$rates = self::$rates ?: $this->loadRates();
    }

    public function rate(string $currency): string
    {
        return self::$rates[$currency];
    }

    private function loadRates()
    {
        try {
            $response = $this->client->request('GET', $this->apiUrl);

            if ($response->getStatusCode() !== 200) {
                throw new RuntimeException('Currency exchanger API is not available.');
            }

            return $response->toArray()['rates'];
        }
        // In some cases it makes sense to separate processing of different exceptions
        catch (Exception $e) {
            throw new RuntimeException('Currency exchanger API is not available due to error: ' . $e->getMessage());
        }
    }
}
