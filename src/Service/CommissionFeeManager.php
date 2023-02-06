<?php

declare(strict_types=1);

namespace App\Service;

use App\Contract\FeeCalculatorInterface;

class CommissionFeeManager
{
    private array $calculatorMap;

    public function __construct(private readonly iterable $calculators)
    {
        foreach ($this->calculators as $calculator) {
            $this->calculatorMap[$calculator->getType()] = $calculator;
        }
    }

    /**
     * @param \Generator $data
     * @return array []float
     */
    public function calculate(\Generator $data): array
    {
        $result = [];

        foreach ($data as $row) {
            list($date, $userId, $userType, $operationType, $amount, $currency) = $row;
            $hasCents = $this->hasCents($amount);

            $result[] = $this->getCalculator($operationType)->calculate($date, $userId, $userType, $amount, $currency, $hasCents);
        }

        return $result;
    }

    private function getCalculator($operationType): FeeCalculatorInterface
    {
        return $this->calculatorMap[$operationType];
    }

    private function hasCents(string $amount): bool
    {
        return str_contains($amount, '.');
    }
}
