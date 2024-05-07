<?php

namespace App\Repository;

use App\Entity\WorkItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WorkItem>
 *
 * @method WorkItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method WorkItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method WorkItem[]    findAll()
 * @method WorkItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WorkItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WorkItem::class);
    }
}
