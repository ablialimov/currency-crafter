<?php

declare(strict_types=1);

namespace App\Service;

use Exception;
use RuntimeException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CurrencyExchanger
{
    private array $rates = [];

    public function __construct(
        private readonly string $apiUrl,
        private readonly HttpClientInterface $client
    ) {
    }

    public function rate(string $currency): string
    {
        if (!$this->rates) {
            $this->rates = $this->loadRates();
        }

        return (string)$this->rates[$currency];
    }

    private function loadRates()
    {
        try {
            $response = $this->client->request('GET', $this->apiUrl);

            if ($response->getStatusCode() !== 200) {
                throw new RuntimeException('Currency exchanger API is not available.');
            }

            return $response->toArray()['rates'];
        } catch (Exception $e) {
            throw new RuntimeException(sprintf('Currency exchanger API is not available due to error: %s', $e->getMessage()));
        }
    }
}
