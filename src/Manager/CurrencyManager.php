<?php
declare(strict_types=1);

namespace App\Manager;

use App\Entity\Currency;
use Doctrine\ORM\EntityManagerInterface;

class CurrencyManager
{
    use ManagerTrait;

    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function create(string $alias, float $value): Currency
    {
        $currency = (new Currency())
            ->setAlias($alias)
            ->setValue($value);
        $this->em->persist($currency);

        return $currency;
    }
}
