<?php
declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class RigDTO
{
    public const STEP_NAME                    = 'step_name';
    public const STEP_ELECTRICITY_COST        = 'step_electricity_cost';
    public const STEP_POWER_SUPPLY_EFFICIENCY = 'step_power_supply_efficiency';
    public const STEP_MOTHERBOARD_CONSUMPTION = 'step_motherboard_consumption';

    private ?int $id;

    #[Assert\NotNull(groups: [self::STEP_NAME])]
    #[Assert\Length(min: 3, groups: [self::STEP_NAME])]
    private ?string $name;

    #[Assert\NotNull(message: 'Введите число', groups: [self::STEP_ELECTRICITY_COST])]
    #[Assert\Positive(groups: [self::STEP_ELECTRICITY_COST])]
    #[Assert\Type('float', groups: [self::STEP_ELECTRICITY_COST])]
    private ?float $electricityCost;

    #[Assert\NotNull(message: 'Введите целое число',groups: [self::STEP_POWER_SUPPLY_EFFICIENCY])]
    #[Assert\Positive(groups: [self::STEP_POWER_SUPPLY_EFFICIENCY])]
    #[Assert\Type('int', groups: [self::STEP_POWER_SUPPLY_EFFICIENCY])]
    private ?int $powerSupplyEfficiency;

    #[Assert\NotNull(message: 'Введите целое число',groups: [self::STEP_MOTHERBOARD_CONSUMPTION])]
    #[Assert\Positive(groups: [self::STEP_MOTHERBOARD_CONSUMPTION])]
    #[Assert\Type('int', groups: [self::STEP_MOTHERBOARD_CONSUMPTION])]
    private ?int $motherboardConsumption;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getElectricityCost(): ?float
    {
        return $this->electricityCost;
    }

    public function setElectricityCost(?float $electricityCost): self
    {
        $this->electricityCost = $electricityCost;

        return $this;
    }

    public function getPowerSupplyEfficiency(): ?int
    {
        return $this->powerSupplyEfficiency;
    }

    public function setPowerSupplyEfficiency(?int $powerSupplyEfficiency): self
    {
        $this->powerSupplyEfficiency = $powerSupplyEfficiency;

        return $this;
    }

    public function getMotherboardConsumption(): ?int
    {
        return $this->motherboardConsumption;
    }

    public function setMotherboardConsumption(?int $motherboardConsumption): self
    {
        $this->motherboardConsumption = $motherboardConsumption;

        return $this;
    }
}
