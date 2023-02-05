<?php

namespace App\Tests\Service;

use App\Service\CommissionFeeManager;
use App\Service\CurrencyExchanger;
use PHPUnit\Framework\MockObject\MockBuilder;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CommissionFeeManagerTest extends KernelTestCase
{
    private const TEST_DATA = [
        ['2014-12-31','4','private','withdraw','1200.00','EUR'],
        ['2015-01-01','4','private','withdraw','1000.00','EUR'],
        ['2016-01-05','4','private','withdraw','1000.00','EUR'],
        ['2016-01-05','1','private','deposit','200.00','EUR'],
        ['2016-01-06','2','business','withdraw','300.00','EUR'],
        ['2016-01-06','1','private','withdraw','30000','JPY'],
        ['2016-01-07','1','private','withdraw','1000.00','EUR'],
        ['2016-01-07','1','private','withdraw','100.00','USD'],
        ['2016-01-10','1','private','withdraw','100.00','EUR'],
        ['2016-01-10','2','business','deposit','10000.00','EUR'],
        ['2016-01-10','3','private','withdraw','1000.00','EUR'],
        ['2016-02-15','1','private','withdraw','300.00','EUR'],
        ['2016-02-19','5','private','withdraw','3000000','JPY'],
    ];

    private const EXPECTED_RESULT = ['0.60', '3.00', '0', '0.06', '1.50', '0', '0.69', '0.30', '0.30', '3.00', '0', '0', '8607.39'];

    public function testCalculate(): void
    {
        self::bootKernel();
        /* @var CommissionFeeManager $feeManager */
        $feeManager = static::getContainer()->get(CommissionFeeManager::class);

        // To avoid FLAKY test
        $this->mockCurrencyExchanger();

        $this->assertEquals(self::EXPECTED_RESULT, $feeManager->calculate(self::TEST_DATA));
    }

    private function mockCurrencyExchanger(): void
    {
        $currencyExchanger = $this->getMockBuilder(CurrencyExchanger::class)->disableOriginalConstructor()->getMock();
        $currencyExchanger->method('rate')
            ->willReturnCallback(static function ($currency) {
                return [
                    'USD' => '1.129031',
                    'JPY' => '130.869977'
                ][$currency];
            });

    }
}
