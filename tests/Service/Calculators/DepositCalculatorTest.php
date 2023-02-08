<?php

namespace App\Tests\Service\Calculators;

use App\Service\Calculators\DepositCalculator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DepositCalculatorTest extends KernelTestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testCalculate(array $data): void
    {
        list($date, $userId, $userType, $amount, $currency, $hasCents, $expectedResult) = $data;

        $calculator = new DepositCalculator('0.03', 2);

        $actual = $calculator->calculate($date, $userId, $userType, $amount, $currency, $hasCents);

        $this->assertEquals($expectedResult, $actual);
        $this->assertEquals($expectedResult, $actual);
    }

    public static function dataProvider()
    {
        yield [['2016-01-05','1','private','200.00','EUR','true','0.06']];
        yield [['2016-01-06','2','business','10000.00','EUR','true','3.00']];
    }
}
