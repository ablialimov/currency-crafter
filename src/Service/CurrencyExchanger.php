<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class CurrencyExchanger
{
    private const API_URL = 'https://developers.paysera.com/tasks/api/currency-exchange-rates';

    public function __construct(private readonly HttpClientInterface $client)
    {
    }

    public function rate(string $currency): string
    {
        // @todo use cache

        $response = $this->client->request('GET', static::API_URL);

        if (!$response->getStatusCode() === 200) {
            throw new \RuntimeException('Currency exchanger API is not available.');
        }

        $content = $response->toArray();

        return $content['rates'][$currency];
    }
}
