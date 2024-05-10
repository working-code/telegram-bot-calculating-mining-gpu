<?php

namespace App\Repository;

use App\Entity\Gpu;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Gpu>
 *
 * @method Gpu|null find($id, $lockMode = null, $lockVersion = null)
 * @method Gpu|null findOneBy(array $criteria, array $orderBy = null)
 * @method Gpu[]    findAll()
 * @method Gpu[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GpuRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Gpu::class);
    }

    /**
     * @return string[]
     */
    public function getAllGpuAlias(): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('g.alias')
            ->from(Gpu::class, 'g');

        return $qb->getQuery()->getResult(AbstractQuery::HYDRATE_SCALAR_COLUMN);
    }

    /**
     * @return Gpu[]
     */
    public function getAllWithAlias(): array
    {
        $qb = $this->createQueryBuilder('g', 'g.alias');

        return $qb->getQuery()->getResult();
    }

    /**
     * @return Gpu[]
     */
    public function getAllGpuWithWorks(): array
    {
        $qb = $this->createQueryBuilder('g');
        $qb->join('g.works', 'w', Join::WITH)
            ->addSelect('w');

        return $qb->getQuery()->getResult();
    }
}
