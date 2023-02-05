<?php

declare(strict_types=1);

namespace App\Service\Calculators;

use App\Contract\FeeCalculatorInterface;
use App\Service\CurrencyExchanger;
use DateTime;

class WithdrawCalculator implements FeeCalculatorInterface
{
    private array $withdrawFrequency = [];

    public function __construct(
        private string $privateClientFee,
        private string $businessClientFee,
        private string $privateClientFreeAmount,
        private string $privateClientFreeWithdraws,
        private readonly CurrencyExchanger $currencyExchanger,
        private readonly int $feePrecision,
        private readonly string $defaultCurrency
    ) {
        bcscale($feePrecision);
    }

    public function getType(): string
    {
        return 'withdraw';
    }

    public function calculate(string $date, string $userId, string $userType, float $amount, string $currency): string
    {
        if (static::USER_TYPE_BUSINESS === $userType) {
            return $this->calcBusinessClientFee($amount);
        }

        $amountInDefaultCurrency = $this->convertToDefaultCurrency($amount, $currency);
        $weekId = $this->getWeekId($date);
        $userKey = $userId . '.' . $weekId;

        $this->withdrawFrequency[$userKey] ??= ['sum' => 0.0, 'frequency' => 0];
        $this->withdrawFrequency[$userKey]['sum'] += $amountInDefaultCurrency;
        $this->withdrawFrequency[$userKey]['frequency']++;

        if ($this->withdrawFrequency[$userKey]['sum'] > $this->privateClientFreeAmount) {
            $notFreeAmount = $this->withdrawFrequency[$userKey]['sum'] - $this->privateClientFreeAmount;

            if ($notFreeAmount >= $amountInDefaultCurrency) {
                return $this->calcPrivateClientFee($amount);
            } else {
                return $this->calcPrivateClientFee(
                    $this->convertToCurrency($notFreeAmount, $currency)
                );
            }
        }

        if ($this->withdrawFrequency[$userKey]['frequency'] <= $this->privateClientFreeWithdraws) {
            return '0.00';
        }

        return $this->calcPrivateClientFee($amount);
    }

    private function calcBusinessClientFee(float $amount): string
    {
        return bcmul((string)round((($this->businessClientFee * $amount) / 100), $this->feePrecision), '1');
    }

    private function calcPrivateClientFee(float $amount): string
    {
        return bcmul((string)round((($this->privateClientFee * $amount) / 100), $this->feePrecision), '1');
    }

    private function convertToDefaultCurrency(float $amount, string $currency): float
    {
        if ($this->defaultCurrency === $currency) {
            return $amount;
        }

        return (float)$amount / $this->currencyExchanger->rate($currency);
    }

    private function convertToCurrency(float $amount, string $currency): float
    {
        if ($this->defaultCurrency === $currency) {
            return $amount;
        }

        return (float)$amount * $this->currencyExchanger->rate($currency);
    }

    private function getWeekId(string $date): string
    {
        $convertedDate = strtotime($date);

        return date("Y-m-d", strtotime('monday this week', $convertedDate)).
            '.'.
            date("Y-m-d", strtotime('sunday this week', $convertedDate));
    }
}
