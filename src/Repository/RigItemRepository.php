<?php

namespace App\Repository;

use App\Entity\RigItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RigItem>
 *
 * @method RigItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method RigItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method RigItem[]    findAll()
 * @method RigItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RigItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RigItem::class);
    }
}
