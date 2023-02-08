<?php

declare(strict_types=1);

namespace App\Service\Calculators;

trait FeeCalculatorTrait
{
    protected function roundFeeCommission(string $value, int $precision, bool $hasCents): string
    {
        if (!$hasCents) {
            return bcadd($value, '1');
        }

        list($intPart, $fractionalPart) = explode('.', $value);

        $newFractionalPart = [];
        for ($i = 0; $i < strlen($fractionalPart); $i++) {
            $newFractionalPart[] = $fractionalPart[$i];

            if ($i + 1 <= $precision || $fractionalPart[$i] <= 0) {
                continue;
            }

            $newFractionalPart = implode('', $newFractionalPart);
            $newFractionalPart = substr($newFractionalPart, 0, $precision + 1);
            $subPart = substr($newFractionalPart, 0, $precision);

            if (str_starts_with($subPart, '0') && substr($newFractionalPart, -1) > 0) {
                $lastNumOfSubPart = substr($subPart, -1);
                $newFractionalPart = substr_replace($subPart, (string)($lastNumOfSubPart + 1), -1);
            } elseif(substr($newFractionalPart, -1) > 0) {
                $newFractionalPart = bcadd($subPart, '1');
            } else {
                $newFractionalPart = substr($newFractionalPart, 0, $precision);
            }

            break;
        }

        if (is_array($newFractionalPart)) {
            return sprintf("%0.{$precision}f", $intPart . '.' . implode('', $newFractionalPart));
        }

        return sprintf("%0.{$precision}f", $intPart . '.' . $newFractionalPart);
    }
}
