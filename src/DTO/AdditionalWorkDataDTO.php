<?php
declare(strict_types=1);

namespace App\DTO;

class AdditionalWorkDataDTO
{
    private array $overclockSettings;
    private array $minerSettings;

    public function getOverclockSettings(): array
    {
        return $this->overclockSettings;
    }

    public function setOverclockSettings(array $overclockSettings): self
    {
        $this->overclockSettings = $overclockSettings;

        return $this;
    }

    public function getMinerSettings(): array
    {
        return $this->minerSettings;
    }

    public function setMinerSettings(array $minerSettings): self
    {
        $this->minerSettings = $minerSettings;

        return $this;
    }
}
