<?php
declare(strict_types=1);

namespace App\DTO;

use App\Entity\Enum\GpuBrand;
use Symfony\Component\Validator\Constraints as Assert;

class GpuDTO
{
    public const STEP_COUNT  = 'step_count';

    private int      $id;
    private string   $name;
    private string   $alias;
    private GpuBrand $brand;

    #[Assert\NotNull(message: 'Введите целое число', groups: [self::STEP_COUNT])]
    #[Assert\Positive(groups: [self::STEP_COUNT])]
    #[Assert\Type('int', groups: [self::STEP_COUNT])]
    private ?int $count;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getCount(): ?int
    {
        return $this->count;
    }

    public function setCount(?int $count): self
    {
        $this->count = $count;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAlias(): string
    {
        return $this->alias;
    }

    public function setAlias(string $alias): self
    {
        $this->alias = $alias;

        return $this;
    }

    public function getBrand(): GpuBrand
    {
        return $this->brand;
    }

    public function setBrand(GpuBrand $brand): self
    {
        $this->brand = $brand;

        return $this;
    }
}
