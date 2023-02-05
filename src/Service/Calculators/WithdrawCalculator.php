<?php

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
        private readonly CurrencyExchanger $currencyExchanger
    )
    {}

    public function getType(): string
    {
        return 'withdraw';
    }

    public function calculate(string $date, string $userId, string $userType, string $amount, string $currency): string
    {
        if (static::USER_TYPE_BUSINESS === $userType) {
            return $this->calcBusinessClientFee($amount);
        }

        $amountEuro = $this->convertToEuro($amount, $currency);
        $weekId = $this->getWeekId($date);
        $userKey = $userId . '.' . $weekId;

        $this->withdrawFrequency[$userKey] ??= ['sum' => 0.0, 'frequency' => 0];
        $this->withdrawFrequency[$userKey]['sum'] += $amountEuro;
        $this->withdrawFrequency[$userKey]['frequency']++;

        if ($this->withdrawFrequency[$userKey]['sum'] > $this->privateClientFreeAmount) {
            $notFreeAmount = $this->withdrawFrequency[$userKey]['sum'] - $this->privateClientFreeAmount;

            if ($notFreeAmount >= $amountEuro) {
                return $this->calcPrivateClientFee($amount);
            }
            else {
                return $this->calcPrivateClientFee(
                    $this->convertToCurrency($notFreeAmount, $currency)
                );
            }
        }

        // There is clarification required. They say: any 4th withdraw must be charged, but then say up to 1000 fee is not applied
        if ($this->withdrawFrequency[$userKey]['frequency'] <= $this->privateClientFreeWithdraws) {
            return 0.0;
        }

        return $this->calcPrivateClientFee($amount);
    }

    private function calcBusinessClientFee(float $amount): string
    {
        return number_format(((double)$this->businessClientFee * $amount) / 100, 2, '.', '');
    }

    private function calcPrivateClientFee(float $amount): string
    {
        return number_format(((double)$this->privateClientFee * $amount) / 100, 2, '.', '');
    }

    private function convertToEuro($amount, $currency): float
    {
        if (static::CURRENCY_EUR === $currency) {
            return $amount;
        }

        return (float)$amount / $this->currencyExchanger->rate($currency);
    }

    private function convertToCurrency($amount, $currency): float
    {
        if (static::CURRENCY_EUR === $currency) {
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