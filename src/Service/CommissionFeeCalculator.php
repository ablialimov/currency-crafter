<?php

namespace App\Service;

class CommissionFeeCalculator
{
    private const OPERATION_TYPE_DEPOSIT = 'deposit';
    private const OPERATION_TYPE_WITHDRAW = 'withdraw';

    private const USER_TYPE_PRIVATE = 'private';
    private const USER_TYPE_BUSINESS = 'business';

    private const CURRENCY_EUR = 'EUR';

    private const DEPOSIT_PERCENT_CHARGE = 0.03;
    private const COMMISSION_FEE_FOR_PRIVATE_CLIENT = 0.3;
    private const COMMISSION_FEE_FOR_BUSINESS_CLIENT = 0.5;

    public function __construct(private readonly CurrencyExchanger $currencyExchanger)
    {
    }

    /**
     * @param array $data
     * @return array []float
     */
    public function calculate(array $data): array
    {
        $result = [];

        foreach ($data as $row) {
            list($date, $userId, $userType, $operationType, $amount, $currency) = $row;

            if ($currency !== static::CURRENCY_EUR) {
                $amount = $this->currencyExchanger->rate($currency);
            }

            // @todo replace with strategy pattern
            if ($operationType === static::OPERATION_TYPE_DEPOSIT) {
                $result[] = $this->calcDeposit($amount);
            } else {
                $result[] = $this->calcWithdraw($amount, $userType);
            }
        }

        return $result;
    }

    private function calcDeposit(float $amount): float
    {
        return number_format((static::DEPOSIT_PERCENT_CHARGE * $amount) / 100, 2);
    }

    private function calcWithdraw(float $amount, string $userType): float
    {
        if ($userType === static::USER_TYPE_PRIVATE) {
            return number_format((static::COMMISSION_FEE_FOR_PRIVATE_CLIENT * $amount) / 100, 2);
        } else {
            return number_format((static::COMMISSION_FEE_FOR_BUSINESS_CLIENT * $amount) / 100, 2);
        }
    }
}
