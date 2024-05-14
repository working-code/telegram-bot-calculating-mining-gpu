<?php
declare(strict_types=1);

namespace App\Parser;

use App\DTO\CurrencyDTO;
use App\Exception\ErrorGetPageException;
use App\Exception\NotFoundException;
use App\Helper\ValueFilterHelper;
use App\Service\Cache;
use App\Service\CurrencyService;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Throwable;

readonly class CurrencyParser
{
    use ParserTrait;

    public function __construct(
        private string                 $currencyUrl,
        private Client                 $httpClient,
        private CurrencyService        $currencyService,
        private LoggerInterface        $logger,
        private ValueFilterHelper      $valueFilterHelper,
        private TagAwareCacheInterface $cache,
    ) {
    }

    public function updateCurrency(): void
    {
        try {
            $usdCurrency = $this->currencyService->getUsdCurrency();
        } catch (NotFoundException $e) {
            $usdCurrency = null;
        }

        try {
            $currencyList = $this->getCurrencyList();
            $usdCurrencyDTO = (new CurrencyDTO())
                ->setAlias('USD')
                ->setValue($this->valueFilterHelper->getFloatFrom($currencyList['USD']) ?? -1);

            if (
                $usdCurrency
                && $usdCurrency->getValue() !== $usdCurrencyDTO->getValue()
            ) {
                $this->currencyService->updateCurrencyByCurrencyDTO($usdCurrency, $usdCurrencyDTO);
                $this->cache->invalidateTags([Cache::CACHE_TAG_CURRENCY_USD, Cache::CACHE_TAG_CALCULATION_BY_RIG]);
            } elseif (!$usdCurrency) {
                $this->currencyService->createCurrencyByCurrencyDTO($usdCurrencyDTO);
            }
        } catch (Throwable $exception) {
            $this->logger->error($exception->getMessage());
        }
    }

    /**
     * @throws ErrorGetPageException
     */
    private function getCurrencyList(): array
    {
        $page = $this->getPage($this->currencyUrl . '/currency_base/daily/');
        $crawler = new Crawler($page);

        $crawlerTr = $crawler->filter('table.data')->filter('tr');
        $crawlerTr = $crawlerTr->reduce(static fn (Crawler $node) => $node->filter('td')->count() > 0);
        $currencyList = [];

        for ($i = 0; $crawlerTr->count() > $i; $i++) {
            $alias = $crawlerTr->eq($i)->filter('td')->eq(1)->text();
            $value = $crawlerTr->eq($i)->filter('td')->eq(4)->text();
            $currencyList[mb_strtoupper($alias)] = $value;
        }

        return $currencyList;
    }
}
