<?php

namespace App\Service\Calculators;

use App\Contract\FeeCalculatorInterface;

class DepositCalculator implements FeeCalculatorInterface
{
    public function __construct(private readonly string $depositPercentFee)
    {
    }

    public function getType(): string
    {
        return 'deposit';
    }

    public function calculate(string $date, string $userId, string $userType, string $amount, string $currency): string
    {
        return number_format(((double)$this->depositPercentFee * $amount) / 100, 2, '.', '');
    }
}
