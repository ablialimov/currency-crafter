<?php

namespace App\Contract;

interface FeeCalculatorInterface
{
    // Put these constants here coz they look to be applicable to any type of operations
    const CURRENCY_EUR = 'EUR';

    const USER_TYPE_PRIVATE = 'private';
    const USER_TYPE_BUSINESS = 'business';

    public function getType(): string;

    public function calculate(string $date, string $userId, string $userType, string $amount, string $currency): string;
}
