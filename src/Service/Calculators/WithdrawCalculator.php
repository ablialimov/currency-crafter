<?php

declare(strict_types=1);

namespace App\Service\Calculators;

use App\Contract\FeeCalculatorInterface;
use App\Service\CurrencyExchanger;
use DateTime;

class WithdrawCalculator implements FeeCalculatorInterface
{
    use FeeCalculatorTrait;

    private array $withdrawFrequency = [];

    public function __construct(
        private readonly string $privateClientFee,
        private readonly string $businessClientFee,
        private readonly string $privateClientFreeAmount,
        private readonly string $privateClientFreeWithdraws,
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

        if (static::USER_TYPE_BUSINESS === $userType) {
            return $this->calcBusinessClientFee($amount, $scale, $hasCents);
        }

        $amountInDefaultCurrency = $this->convertToDefaultCurrency($amount, $currency);
        $weekId = $this->getWeekId($date);
        $userKey = $userId . '.' . $weekId;

        $this->withdrawFrequency[$userKey] ??= ['sum' => '0', 'frequency' => '0'];
        $this->withdrawFrequency[$userKey]['sum'] = bcadd($this->withdrawFrequency[$userKey]['sum'], $amountInDefaultCurrency, $scale);
        $this->withdrawFrequency[$userKey]['frequency'] = bcadd($this->withdrawFrequency[$userKey]['frequency'], '1', $scale);

        if ($this->withdrawFrequency[$userKey]['sum'] <= $this->privateClientFreeAmount && $this->withdrawFrequency[$userKey]['frequency'] <= $this->privateClientFreeWithdraws) {
            $result = $hasCents ? sprintf("%0.{$this->feePrecision}f", 0) : '0';
        } elseif ($this->withdrawFrequency[$userKey]['sum'] > $this->privateClientFreeAmount && $this->withdrawFrequency[$userKey]['frequency'] <= $this->privateClientFreeWithdraws) {
            $notFreeAmount = bcsub($this->withdrawFrequency[$userKey]['sum'], $this->privateClientFreeAmount, $scale);

            if ($notFreeAmount >= $amountInDefaultCurrency) {
                $result = $this->calcPrivateClientFee($amount, $scale, $hasCents);
            } else {
                $result = $this->calcPrivateClientFee($this->convertToCurrency($notFreeAmount, $currency), $scale, $hasCents);
            }
        } else {
            $result = $this->calcPrivateClientFee($amount, $scale, $hasCents);
        }

        return $result;
    }

    private function calcBusinessClientFee(string $amount, int $scale, bool $hasCents): string
    {
        return $this->roundFeeCommission(bcdiv(bcmul($this->businessClientFee, $amount, static::CALC_PRECISION), '100', static::CALC_PRECISION), $scale, $hasCents);
    }

    private function calcPrivateClientFee(string $amount, int $scale, bool $hasCents): string
    {
        return $this->roundFeeCommission(bcdiv(bcmul($this->privateClientFee, $amount, static::CALC_PRECISION), '100', static::CALC_PRECISION), $scale, $hasCents);
    }

    private function convertToDefaultCurrency(string $amount, string $currency): string
    {
        if ($this->defaultCurrency === $currency) {
            return $amount;
        }

        return bcdiv($amount, $this->currencyExchanger->rate($currency), static::CALC_PRECISION);
    }

    private function convertToCurrency(string $amount, string $currency): string
    {
        if ($this->defaultCurrency === $currency) {
            return $amount;
        }

        return bcmul($amount, $this->currencyExchanger->rate($currency), static::CALC_PRECISION);
    }

    private function getWeekId(string $date): string
    {
        $convertedDate = strtotime($date);

        return date("Y-m-d", strtotime('monday this week', $convertedDate)).
            '.'.
            date("Y-m-d", strtotime('sunday this week', $convertedDate));
    }
}
