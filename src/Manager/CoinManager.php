<?php
declare(strict_types=1);

namespace App\Manager;

use App\Entity\Coin;
use Doctrine\ORM\EntityManagerInterface;

readonly class CoinManager
{
    use ManagerTrait;

    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    public function create(string $name, string $alias, float $price, ?string $algorithm): Coin
    {
        $coin = (new Coin())
            ->setName($name)
            ->setAlias($alias)
            ->setPrice($price)
            ->setAlgorithm($algorithm);
        $this->em->persist($coin);

        return $coin;
    }
}
