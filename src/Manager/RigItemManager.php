<?php
declare(strict_types=1);

namespace App\Manager;

use App\Entity\Gpu;
use App\Entity\Rig;
use App\Entity\RigItem;
use Doctrine\ORM\EntityManagerInterface;

readonly class RigItemManager
{
    use ManagerTrait;

    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    public function create(Rig $rig, Gpu $gpu, int $count): RigItem
    {
        $rigItem = (new RigItem())
            ->setRig($rig)
            ->setGpu($gpu)
            ->setCount($count);
        $this->em->persist($rigItem);

        $rig->addItem($rigItem);

        return $rigItem;
    }
}
