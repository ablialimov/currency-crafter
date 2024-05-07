<?php

declare(strict_types=1);

namespace App\Contract;

use App\Dto\AccountOperation;

interface FeeCalculatorInterface
{
    const CALC_PRECISION = 4;

    const USER_TYPE_PRIVATE = 'private';
    const USER_TYPE_BUSINESS = 'business';

    public function getType(): string;

    public function calculate(AccountOperation $accountOperation): string;
}
