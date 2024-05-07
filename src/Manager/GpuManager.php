<?php
declare(strict_types=1);

namespace App\Manager;

use App\Entity\Enum\GpuBrand;
use App\Entity\Gpu;
use Doctrine\ORM\EntityManagerInterface;

readonly class GpuManager
{
    use ManagerTrait;

    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    public function create(string $name, GpuBrand $brand, string $alias): Gpu
    {
        $gpu = (new Gpu())
            ->setName($name)
            ->setBrand($brand)
            ->setAlias($alias);
        $this->em->persist($gpu);

        return $gpu;
    }
}
