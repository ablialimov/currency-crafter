<?php

namespace App\Tests\Service;

use App\Service\CurrencyExchanger;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CurrencyExchangerTest extends KernelTestCase
{
    private const EXPECTED_RESULT = 1.129031;

    public function testRateError(): void
    {
        self::bootKernel();

        $this->expectException(\RuntimeException::class);

        $response = $this->createMock(MockResponse::class);
        $response->method('toArray')->willReturn([]);
        $response->method('getStatusCode')->willReturn(500);

        $client = $this->createMock(HttpClientInterface::class);
        $client->method('request')->willReturn($response);

        $containerBag = $this->createMock(ContainerBagInterface::class);

        $currencyExchanger = new CurrencyExchanger('http://exchanger-api-url', $client, $containerBag);
        $currencyExchanger->rate('USD');
    }

    public function testRate(): void
    {
        self::bootKernel();

        $response = $this->createMock(MockResponse::class);
        $response->method('toArray')->willReturn(['rates' => ['USD' => static::EXPECTED_RESULT, 'JPY' => 130.869977]]);
        $response->method('getStatusCode')->willReturn(200);

        $client = $this->createMock(HttpClientInterface::class);
        $client->method('request')->willReturn($response);

        $containerBag = $this->createMock(ContainerBagInterface::class);

        $currencyExchanger = new CurrencyExchanger('http://exchanger-api-url', $client, $containerBag);

        $this->assertEquals(static::EXPECTED_RESULT, $currencyExchanger->rate('USD'));
    }
}
