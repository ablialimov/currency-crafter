<?php

declare(strict_types=1);

namespace App\Service;

use App\Contract\FeeCalculatorInterface;
use App\Dto\AccountOperation;

class CommissionFeeManager
{
    private array $calculatorMap;

    public function __construct(private readonly iterable $calculators)
    {
        foreach ($this->calculators as $calculator) {
            $this->calculatorMap[$calculator->getType()] = $calculator;
        }
    }

    public function calculate(\Generator $data): \Generator
    {
        foreach ($data as $row) {
            list($date, $userId, $userType, $operationType, $amount, $currency) = $row;
            $hasCents = $this->hasCents($amount);

            yield $this->getCalculator($operationType)->calculate(
                new AccountOperation(
                    $date,
                    $userId,
                    $userType,
                    $amount,
                    $currency,
                    $hasCents
                )
            );
        }
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
