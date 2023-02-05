<?php

declare(strict_types=1);

namespace App\Service\Calculators;

use App\Contract\FeeCalculatorInterface;

class DepositCalculator implements FeeCalculatorInterface
{
    public function __construct(private readonly string $depositPercentFee, private readonly int $feePrecision)
    {
        bcscale($feePrecision);
    }

    public function getType(): string
    {
        return 'deposit';
    }

    public function calculate(string $date, string $userId, string $userType, float $amount, string $currency): string
    {
        return bcmul((string)round((($this->depositPercentFee * $amount) / 100), $this->feePrecision), '1');
    }
}
