<?php

declare(strict_types=1);

namespace App\Service\Calculators;

use App\Contract\FeeCalculatorInterface;

class DepositCalculator implements FeeCalculatorInterface
{
    public function __construct(private readonly string $depositPercentFee, private readonly int $feePrecision)
    {
    }

    public function getType(): string
    {
        return 'deposit';
    }

    public function calculate(string $date, string $userId, string $userType, string $amount, string $currency, bool $hasCents): string
    {
        bcscale($hasCents ? $this->feePrecision : 0);

        return bcdiv(bcmul($this->depositPercentFee, $amount), '100');
    }
}
