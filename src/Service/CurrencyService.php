<?php
declare(strict_types=1);

namespace App\Service;

use App\DTO\CurrencyDTO;
use App\Entity\Currency;
use App\Exception\NotFoundException;
use App\Exception\ValidationErrorException;
use App\Manager\CurrencyManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

readonly class CurrencyService
{
    private const CONVERT_SCALE = 4;

    public function __construct(
        private EntityManagerInterface $em,
        private CurrencyManager        $currencyManager,
        private ValidatorInterface     $validator,
        private TagAwareCacheInterface $cache,
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
     * @throws InvalidArgumentException
     */
    public function convertRubInUsd(float $rub): float
    {
        $result = bcdiv((string)$rub, $this->getUsdValue(), self::CONVERT_SCALE);

        return (float)$result;
    }

    /**
     * @throws InvalidArgumentException
     */
    private function getUsdValue(): string
    {
        return $this->cache->get('currency_usd', function (ItemInterface $item): string {
            $item->tag(Cache::CACHE_TAG_CURRENCY_USD);

            return (string)$this->getUsdCurrency()->getValue();
        });
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

    /**
     * @throws InvalidArgumentException
     */
    public function convertUsdInRub(float $usd): float
    {
        $result = bcmul(sprintf("%.4F", $usd), $this->getUsdValue(), self::CONVERT_SCALE);

        return (float)$result;
    }
}
