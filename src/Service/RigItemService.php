<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\RigItem;
use App\Manager\RigItemManager;
use Doctrine\ORM\EntityManagerInterface;

readonly class RigItemService
{
    public function __construct(
        private EntityManagerInterface $em,
        private RigItemManager         $rigItemManager,
    ) {
    }

    public function removeRigItemByIds(array $ids): void
    {
        $rigItemRepository = $this->em->getRepository(RigItem::class);
        $rigItems = $rigItemRepository->findBy(['id' => $ids]);

        foreach ($rigItems as $rigItem) {
            $this->rigItemManager->remove($rigItem);
        }

        $this->rigItemManager->emFlush();
    }
}
