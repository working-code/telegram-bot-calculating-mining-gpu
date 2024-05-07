<?php
declare(strict_types=1);

namespace App\Service;

use App\DTO\CoinDTO;
use App\Entity\Coin;
use App\Exception\ValidationErrorException;
use App\Manager\CoinManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class CoinService
{
    public function __construct(
        private EntityManagerInterface $em,
        private CoinManager            $coinManager,
        private ValidatorInterface     $validator,
    ) {
    }

    public function getCoinManager(): CoinManager
    {
        return $this->coinManager;
    }

    public function getAllCoinWithAliasIndex(): array
    {
        $coinRepository = $this->em->getRepository(Coin::class);

        return $coinRepository->getAllWithAliasIndex();
    }

    /**
     * @throws ValidationErrorException
     */
    public function createCoinByCoinDTO(CoinDTO $coinDTO, $flush = true): Coin
    {
        $coin = $this->coinManager->create(
            $coinDTO->getName(),
            $coinDTO->getAlias(),
            $coinDTO->getPrice(),
            $coinDTO->getAlgorithm()
        );
        $errors = $this->validator->validate($coin);

        if ($errors->count()) {
            throw new ValidationErrorException($errors);
        }

        if ($flush) {
            $this->em->flush();
        }

        return $coin;
    }

    /**
     * @throws ValidationErrorException
     */
    public function updateCoinByCoinDTO(Coin $coin, CoinDTO $coinDTO, $flush = true): Coin
    {
        $coin->setPrice($coinDTO->getPrice())
            ->setName($coinDTO->getName())
            ->setAlgorithm($coinDTO->getAlgorithm());
        $errors = $this->validator->validate($coin);

        if ($errors->count()) {
            throw new ValidationErrorException($errors);
        }

        if ($flush) {
            $this->em->flush();
        }

        return $coin;
    }
}
