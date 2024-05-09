<?php
declare(strict_types=1);

namespace App\DTO;

class CurrencyDTO
{
    private string $alias;
    private float  $value;

    public function getAlias(): string
    {
        return $this->alias;
    }

    public function setAlias(string $alias): self
    {
        $this->alias = $alias;

        return $this;
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function setValue(float $value): self
    {
        $this->value = $value;

        return $this;
    }
}
