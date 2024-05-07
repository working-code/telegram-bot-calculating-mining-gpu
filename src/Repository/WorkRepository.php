<?php

namespace App\Repository;

use App\Entity\Gpu;
use App\Entity\Work;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Work>
 *
 * @method Work|null find($id, $lockMode = null, $lockVersion = null)
 * @method Work|null findOneBy(array $criteria, array $orderBy = null)
 * @method Work[]    findAll()
 * @method Work[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WorkRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Work::class);
    }

    /**
     * @return Work[]
     */
    public function getWorksWithItemByGpu(Gpu $gpu): array
    {
        $qb = $this->createQueryBuilder('w', 'w.alias');
        $qb->join('w.items', 'i', Expr\Join::WITH)
            ->addSelect('i')
            ->andWhere($qb->expr()->eq('w.gpu', ':gpu'))
            ->setParameter('gpu', $gpu);

        return $qb->getQuery()->getResult();
    }
}
