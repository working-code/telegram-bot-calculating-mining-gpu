<?php
declare(strict_types=1);

namespace App\Helper;

class ValueFilterHelper
{
    public function getFloatFrom(mixed $value): ?float
    {
        $value = str_replace(',', '.', $value);
        $value = filter_var($value, FILTER_VALIDATE_FLOAT);

        return $value ?: null;
    }

    public function getIntFrom(mixed $value): ?int
    {
        return filter_var($value, FILTER_VALIDATE_INT) ?: null;
    }
}
