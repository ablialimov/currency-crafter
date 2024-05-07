<?php

declare(strict_types=1);

namespace App\Dto;

class AccountOperation
{
    public function __construct(
        public readonly string $date,
        public readonly string $userId,
        public readonly string $userType,
        public readonly string $amount,
        public readonly string $currency,
        public readonly bool $hasCents
    ) {
    }
}
