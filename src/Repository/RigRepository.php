<?php

namespace App\Repository;

use App\Entity\Rig;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Rig>
 *
 * @method Rig|null find($id, $lockMode = null, $lockVersion = null)
 * @method Rig|null findOneBy(array $criteria, array $orderBy = null)
 * @method Rig[]    findAll()
 * @method Rig[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rig::class);
    }

    /**
     * @return Rig[]
     */
    public function findByTelegramId(int $telegramId): array
    {
        $qb = $this->createQueryBuilder('r');
        $qb->join('r.user', 'u')
            ->andWhere($qb->expr()->eq('u.telegramId', $telegramId));

        return $qb->getQuery()->getResult();
    }
}
