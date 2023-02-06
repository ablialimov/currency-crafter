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
    }

    public function getType(): string
    {
        return 'withdraw';
    }

    public function calculate(string $date, string $userId, string $userType, string $amount, string $currency, bool $hasCents): string
    {
        $scale = $hasCents ? $this->feePrecision : 0;
        bcscale($scale);

        if (static::USER_TYPE_BUSINESS === $userType) {
            return $this->calcBusinessClientFee($amount, $scale);
        }

        $amountInDefaultCurrency = $this->convertToDefaultCurrency($amount, $currency);
        $weekId = $this->getWeekId($date);
        $userKey = $userId . '.' . $weekId;

        $this->withdrawFrequency[$userKey] ??= ['sum' => '0', 'frequency' => '0'];
        $this->withdrawFrequency[$userKey]['sum'] = bcadd($this->withdrawFrequency[$userKey]['sum'], $amountInDefaultCurrency);
        $this->withdrawFrequency[$userKey]['frequency'] = bcadd($this->withdrawFrequency[$userKey]['frequency'], '1');

        if ($this->withdrawFrequency[$userKey]['sum'] > $this->privateClientFreeAmount) {
            $notFreeAmount = bcsub($this->withdrawFrequency[$userKey]['sum'], $this->privateClientFreeAmount);

            if ($notFreeAmount >= $amountInDefaultCurrency) {
                return $this->calcPrivateClientFee($amount, $scale);
            } else {
                return $this->calcPrivateClientFee($this->convertToCurrency($notFreeAmount, $currency), $scale);
            }
        }

        if ($this->withdrawFrequency[$userKey]['frequency'] <= $this->privateClientFreeWithdraws) {
            return $hasCents ? '0.00' : '0';
        }

        return $this->calcPrivateClientFee($amount, $scale);
    }

    private function roundFeeCommission(string $value, int $places = 0): string
    {
        if ($places < 0) {
            $places = 0;
        }

        $x = pow(10, $places);
        $result = (string)(($value >= 0 ? ceil($value * $x) : floor($value * $x)) / $x);

        return sprintf("%0.{$places}f", $result);
    }

    private function calcBusinessClientFee(string $amount, int $scale = 0): string
    {
        return $this->roundFeeCommission((string)(bcmul($this->businessClientFee, $amount) / 100), $scale);
    }

    private function calcPrivateClientFee(string $amount, int $scale = 0): string
    {
        return $this->roundFeeCommission((string)(bcmul($this->privateClientFee, $amount) / 100), $scale);
    }

    private function convertToDefaultCurrency(string $amount, string $currency): string
    {
        if ($this->defaultCurrency === $currency) {
            return $amount;
        }

        return bcdiv($amount, $this->currencyExchanger->rate($currency));
    }

    private function convertToCurrency(string $amount, string $currency): string
    {
        if ($this->defaultCurrency === $currency) {
            return $amount;
        }

        return bcmul($amount, $this->currencyExchanger->rate($currency));
    }

    private function getWeekId(string $date): string
    {
        $convertedDate = strtotime($date);

        return date("Y-m-d", strtotime('monday this week', $convertedDate)).
            '.'.
            date("Y-m-d", strtotime('sunday this week', $convertedDate));
    }

    private function isNumeric(string $amount): bool
    {
        return !strpos($amount, '.') !== false;
    }


}
