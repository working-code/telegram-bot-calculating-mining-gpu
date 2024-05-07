<?php
declare(strict_types=1);

namespace App\Manager;

use App\Entity\Gpu;
use App\Entity\Work;
use Doctrine\ORM\EntityManagerInterface;

readonly class WorkManager
{
    use ManagerTrait;

    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    public function create(Gpu $gpu, string $alias): Work
    {
        $work = (new Work())
            ->setGpu($gpu)
            ->setAlias($alias);
        $this->em->persist($work);

        return $work;
    }
}
