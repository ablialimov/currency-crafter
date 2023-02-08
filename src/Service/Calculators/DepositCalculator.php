<?php

declare(strict_types=1);

namespace App\Service\Calculators;

use App\Contract\FeeCalculatorInterface;

class DepositCalculator implements FeeCalculatorInterface
{
    use FeeCalculatorTrait;

    public function __construct(private readonly string $depositPercentFee, private readonly int $feePrecision)
    {
    }

    public function getType(): string
    {
        return 'deposit';
    }

    public function calculate(string $date, string $userId, string $userType, string $amount, string $currency, bool $hasCents): string
    {
        $scale = $hasCents ? $this->feePrecision : 0;

        return $this->roundFeeCommission(bcdiv(bcmul($this->depositPercentFee, $amount, static::CALC_PRECISION), '100', static::CALC_PRECISION), $scale, $hasCents);
    }
}
