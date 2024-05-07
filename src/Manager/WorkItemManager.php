<?php
declare(strict_types=1);

namespace App\Manager;

use App\Entity\Coin;
use App\Entity\Work;
use App\Entity\WorkItem;
use Doctrine\ORM\EntityManagerInterface;

readonly class WorkItemManager
{
    use ManagerTrait;

    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    public function create(
        Work   $work,
        Coin   $coin,
        string $alias,
        string $hashRate,
        float  $count,
        int    $powerConsumption
    ): WorkItem {
        $workItem = (new WorkItem())
            ->setWork($work)
            ->setCoin($coin)
            ->setAlias($alias)
            ->setHashRate($hashRate)
            ->setCount($count)
            ->setPowerConsumption($powerConsumption);
        $this->em->persist($workItem);

        return $workItem;
    }
}
