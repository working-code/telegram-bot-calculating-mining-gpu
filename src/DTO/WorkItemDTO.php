<?php
declare(strict_types=1);

namespace App\DTO;

use App\Entity\Coin;

class WorkItemDTO
{
    private string $alias;
    private string $hashRate;
    private float $count;
    private int $powerConsumption;
    private Coin $coin;

    public function getAlias(): string
    {
        return $this->alias;
    }

    public function setAlias(string $alias): self
    {
        $this->alias = $alias;

        return $this;
    }

    public function getHashRate(): string
    {
        return $this->hashRate;
    }

    public function setHashRate(string $hashRate): self
    {
        $this->hashRate = $hashRate;

        return $this;
    }

    public function getCount(): float
    {
        return $this->count;
    }

    public function setCount(float $count): self
    {
        $this->count = $count;

        return $this;
    }

    public function getPowerConsumption(): int
    {
        return $this->powerConsumption;
    }

    public function setPowerConsumption(int $powerConsumption): self
    {
        $this->powerConsumption = $powerConsumption;

        return $this;
    }

    public function getCoin(): Coin
    {
        return $this->coin;
    }

    public function setCoin(Coin $coin): self
    {
        $this->coin = $coin;

        return $this;
    }
}
