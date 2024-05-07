<?php
declare(strict_types=1);

namespace App\Manager;

use App\Entity\Rig;
use Doctrine\ORM\EntityManagerInterface;

readonly class RigManager
{
    use ManagerTrait;

    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    public function create(
        string $name,
        float  $electricityCost,
        int    $powerSupplyEfficiency,
        int    $motherboardConsumption
    ): Rig {
        $rig = (new Rig())
            ->setName($name)
            ->setElectricityCost($electricityCost)
            ->setPowerSupplyEfficiency($powerSupplyEfficiency)
            ->setMotherboardConsumption($motherboardConsumption);
        $this->em->persist($rig);

        return $rig;
    }
}
