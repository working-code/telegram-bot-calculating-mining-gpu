<?php
declare(strict_types=1);

namespace App\Service;

use App\DTO\CurrencyDTO;
use App\Entity\Currency;
use App\Exception\NotFoundException;
use App\Exception\ValidationErrorException;
use App\Manager\CurrencyManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class CurrencyService
{
    public function __construct(
        private EntityManagerInterface $em,
        private CurrencyManager        $currencyManager,
        private ValidatorInterface     $validator,
    ) {
    }

    /**
     * @throws ValidationErrorException
     */
    public function createCurrencyByCurrencyDTO(CurrencyDTO $currencyDTO, $flush = true): Currency
    {
        $currency = $this->currencyManager->create($currencyDTO->getAlias(), $currencyDTO->getValue());

        $errors = $this->validator->validate($currency);

        if ($errors->count()) {
            throw new ValidationErrorException($errors);
        }

        if ($flush) {
            $this->currencyManager->emFlush();
        }

        return $currency;
    }

    /**
     * @throws ValidationErrorException
     */
    public function updateCurrencyByCurrencyDTO(Currency $currency, CurrencyDTO $currencyDTO, $flush = true): void
    {
        $currency
            ->setAlias($currency->getAlias())
            ->setValue($currencyDTO->getValue());
        $errors = $this->validator->validate($currency);

        if ($errors->count()) {
            throw new ValidationErrorException($errors);
        }

        if ($flush) {
            $this->currencyManager->emFlush();
        }
    }

    /**
     * @throws NotFoundException
     */
    public function getUsdCurrency(): Currency
    {
        $currencyRepository = $this->em->getRepository(Currency::class);
        $currency = $currencyRepository->findOneBy(['alias' => 'USD']);

        if (!$currency) {
            throw new NotFoundException('Currency USD not found');
        }

        return $currency;
    }
}
