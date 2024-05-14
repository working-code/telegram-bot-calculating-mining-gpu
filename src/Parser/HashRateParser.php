<?php
declare(strict_types=1);

namespace App\Parser;

use App\DTO\AdditionalWorkDataDTO;
use App\DTO\CoinDTO;
use App\DTO\GpuDTO;
use App\DTO\WorkDTO;
use App\DTO\WorkItemDTO;
use App\Entity\Enum\GpuBrand;
use App\Entity\Gpu;
use App\Exception\ErrorGetPageException;
use App\Exception\NotFoundException;
use App\Exception\ValidationErrorException;
use App\Helper\ValueFilterHelper;
use App\Service\Cache;
use App\Service\CoinService;
use App\Service\GpuService;
use App\Service\WorkService;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Throwable;

readonly class HashRateParser
{
    use ParserTrait;

    public function __construct(
        private string                 $hashRateUrl,
        private Client                 $httpClient,
        private GpuService             $gpuService,
        private LoggerInterface        $logger,
        private CoinService            $coinService,
        private ValueFilterHelper      $valueFilterHelper,
        private WorkService            $workService,
        private TagAwareCacheInterface $cache,
    ) {
    }

    public function getAdditionalData(): void
    {
        ini_set('memory_limit', '256M');

        try {
            $gpus = $this->gpuService->getAllGpuWithWorks();

            foreach ($gpus as $gpu) {
                $this->logger->info('-- Получаю works для ' . $gpu->getName());
                $this->updateAdditionalWorkDataForGpu($gpu);

                $this->gpuService->getGpuManager()->emFlush();
            }
        } catch (Throwable $exception) {
            $this->logger->error($exception->getMessage());
        }
    }

    /**
     * @throws ErrorGetPageException
     */
    private function updateAdditionalWorkDataForGpu(Gpu $gpu): void
    {
        foreach ($gpu->getWorks() as $work) {
            $url = sprintf(
                '%s/gpus/%s/%s',
                $this->hashRateUrl,
                $gpu->getAlias(),
                $work->getAlias(),
            );

            $pageHtml = $this->getPage($url);
            $crawler = new Crawler($pageHtml);
            $contentCrawler = $crawler->filter('div.contentContainer>div')->eq(3)->filter('div.content');

            //Overclock settings
            $overclockSettingsCrawler = $contentCrawler->eq(1)->filter('table>tr')
                ->reduce(function (Crawler $node) {
                    return $node->matches('tr>td.infoOCDesc');
                });
            $overclockSettingList = [];

            for ($i = 0; $i < $overclockSettingsCrawler->count(); $i++) {
                $name = $overclockSettingsCrawler->eq($i)->filter('td.infoOCDesc')->text();
                $value = $overclockSettingsCrawler->eq($i)->filter('td.infoOC')->innerText();

                $overclockSettingList[$name] = $value;
            }

            //miner settings
            $settingForMinerCrawler = $contentCrawler->eq(3)->filter('div.description>div');
            $minerSettingList = [];

            $valuesCrawler = $settingForMinerCrawler->reduce(fn (Crawler $node) => $node->matches('div>span'));
            $nameCrawler = $settingForMinerCrawler->reduce(fn (Crawler $node) => !$node->matches('div>span'));

            for ($i = 0; $i < $nameCrawler->count(); $i++) {
                $minerSettingList[$nameCrawler->eq($i)->text()] = $valuesCrawler->eq($i)->text();
            }

            $additionalWorkDataDTO = (new AdditionalWorkDataDTO())
                ->setOverclockSettings($overclockSettingList)
                ->setMinerSettings($minerSettingList);

            try {
                $this->workService->updateWorkByAdditionalWorkDataDTO($work, $additionalWorkDataDTO, false);
            } catch (ValidationErrorException $e) {
                $this->logger->error($e->getMessage());

                continue;
            }
        }
    }

    public function getMainData(): void
    {
        ini_set('memory_limit', '256M');

        try {
            $this->logger->info('Начало парсинга');
            $this->logger->info('Обновление gpu');
            $this->updateListGpu();

            $this->logger->info('Обновление монет');
            $this->updateListCoin();

            $this->logger->info('Обновление воркеров:');
            $this->getUpdateWorks();

            $this->logger->info('парсинг завершен');
            $this->cache->invalidateTags([Cache::CACHE_TAG_CALCULATION_BY_RIG]);
        } catch (Throwable $exception) {
            $this->logger->error($exception->getMessage());
        }
    }

    /**
     * @throws ErrorGetPageException
     */
    private function updateListGpu(): void
    {
        $listGpu = $this->getListGpu();
        $newGpuAliasList = array_column($listGpu, 'alias');
        $existGpuAliasList = $this->gpuService->getAllGpuAlias();
        $listAliasForAdd = array_diff($newGpuAliasList, $existGpuAliasList);

        if ($listAliasForAdd) {
            $listGpuForAdd = array_filter($listGpu, static function (array $item) use ($listAliasForAdd): bool {
                return in_array($item['alias'], $listAliasForAdd);
            });

            foreach ($listGpuForAdd as $gpuData) {
                $gpuDTO = (new GpuDTO())
                    ->setName($gpuData['name'])
                    ->setBrand($gpuData['brand'])
                    ->setAlias($gpuData['alias']);
                try {
                    $this->gpuService->createGpuByGpuDTO($gpuDTO, false);
                } catch (ValidationErrorException $e) {
                    $this->logger->error($e->getMessage());
                }
            }

            $this->gpuService->getGpuManager()->emFlush();
        }
    }

    /**
     * @throws ErrorGetPageException
     */
    private function getListGpu(): array
    {
        $pageHtml = $this->getPage($this->hashRateUrl . '/gpus');
        $crawler = new Crawler($pageHtml);
        $ulCrawler = $crawler->filter('#myUL>li')->filter('a.overlay');
        $gpuList = [];

        for ($i = 0; $i < $ulCrawler->count(); $i++) {
            $name = $ulCrawler->eq($i)->text();

            $nameList = explode(' ', $name);
            $brand = mb_strtolower(reset($nameList));
            $brand = match (trim($brand)) {
                'nvidia' => GpuBrand::Nvidia,
                'amd' => GpuBrand::Amd,
                'intel' => GpuBrand::Intel,
                default => null,
            };

            $urlPath = explode('/', $ulCrawler->eq($i)->attr('href'));
            $alias = end($urlPath);

            $gpuList[] = ['name' => trim($name), 'brand' => $brand, 'alias' => trim($alias)];
        }

        return $gpuList;
    }

    /**
     * @throws ErrorGetPageException
     */
    private function updateListCoin(): void
    {
        $newCoinList = $this->getListCoin();
        $currentCoinList = $this->coinService->getAllCoinWithAliasIndex();

        foreach ($newCoinList as $newAlias => $coinDTO) {
            try {
                if (isset($currentCoinList[$newAlias])) {
                    $this->coinService->updateCoinByCoinDTO($currentCoinList[$newAlias], $coinDTO, false);
                } else {
                    $this->coinService->createCoinByCoinDTO($coinDTO, false);
                }
            } catch (ValidationErrorException $e) {
                $this->logger->error($e->getMessage());
            }
        }

        $this->coinService->getCoinManager()->emFlush();
    }

    /**
     * @return CoinDTO[]
     * @throws ErrorGetPageException
     */
    private function getListCoin(): array
    {
        $pageHtml = $this->getPage($this->hashRateUrl . '/coins');
        $crawler = new Crawler($pageHtml);
        $liCrawler = $crawler->filter('#myUL>li')
            ->reduce(static fn (Crawler $node) => $node->filter('table')->count() > 0);
        $coinDTOList = [];

        for ($i = 0; $liCrawler->count() > $i; $i++) {
            $name = $liCrawler->eq($i)->filter('div>span')->innerText();
            $alias = $liCrawler->eq($i)->filter('div>span')->children()->text();

            $algorithmCrawler = $liCrawler->eq($i)->filter('table>tr')->reduce(
                static function (Crawler $node) {
                    $node = $node->filter('td.coinsInfo')?->first();
                    $result = false;

                    if ($node->count() > 0) {
                        $result = strcasecmp($node->text(), 'algorithm') === 0;
                    }

                    return $result;
                }
            );
            $algorithm = $algorithmCrawler->count() > 0
                ? $algorithmCrawler->filter('td.coinsData')->text()
                : null;

            $priceCrawler = $liCrawler->eq($i)->filter('table>tr')->reduce(
                static function (Crawler $node) {
                    $node = $node->filter('td.deviceHeader2');
                    $result = false;

                    if ($node->count() > 1) {
                        $result = strcasecmp($node->text(), 'price') === 0;
                    }

                    return $result;
                });
            $price = $priceCrawler->count() > 0
                ? $priceCrawler->filter('td.deviceHeader2')->eq(1)->text()
                : null;
            $price = is_string($price)
                ? trim(str_replace('$', '', $price))
                : null;

            $coinDTO = (new CoinDTO())
                ->setName($name)
                ->setAlias($alias)
                ->setAlgorithm($algorithm)
                ->setPrice($this->valueFilterHelper->getFloatFrom($price));

            $coinDTOList[$alias] = $coinDTO;
        }

        return $coinDTOList;
    }

    /**
     * @throws ErrorGetPageException
     * @throws NotFoundException
     */
    private function getUpdateWorks(): void
    {
        $gpus = $this->gpuService->getAllGpuWithAlias();
        $coinList = $this->coinService->getAllCoinWithAliasIndex();

        foreach ($gpus as $gpu) {
            $this->logger->info(sprintf(' - обновляю воркеры для gpu %s', $gpu->getName()));

            $newListWork = $this->getListWorkDTO($gpu, $coinList);
            $currentWorkList = $this->workService->getWorksWithItemByGpu($gpu);

            foreach ($newListWork as $newAlias => $workDTO) {
                try {
                    if (isset($currentWorkList[$newAlias])) {
                        $this->workService->updateWorkByWorkDTO($currentWorkList[$newAlias], $workDTO, false);
                    } else {
                        $this->workService->createWorkByWorkDTO($workDTO, false);
                    }
                } catch (ValidationErrorException $e) {
                    $this->logger->error($e->getMessage());
                }
            }

            unset($newListWork, $currentWorkList);
            gc_collect_cycles();
        }

        $this->gpuService->getGpuManager()->emFlush();
    }

    /**
     * @return WorkDTO[]
     * @throws ErrorGetPageException
     * @throws NotFoundException
     */
    private function getListWorkDTO(Gpu $gpu, array $coinList): array
    {
        $pageHtml = $this->getPage($this->hashRateUrl . '/gpus/' . $gpu->getAlias());
        $crawler = new Crawler($pageHtml);
        $ulCrawler = $crawler->filter('#myUL>li')->filter('div.deviceLink');
        $worksDTO = [];

        for ($i = 0; $i < $ulCrawler->count(); $i++) {
            //alias
            $path = $ulCrawler->eq($i)->filter('a.overlay')->attr('href');
            $path = explode('/', $path);
            $alias = trim(end($path));
            $workDTO = (new WorkDTO())
                ->setAlias($alias)
                ->setGpu($gpu);

            //name coin
            $nameCoinCrawler = $ulCrawler->eq($i)->filter('div.w3-rest');
            $coinCount = $nameCoinCrawler->count();

            //coin data
            $deviceDataCrawler = $ulCrawler->eq($i)->filter('div.deviceData');
            $trCrawler = $deviceDataCrawler->eq(0)->filter('table>tr');

            for ($y = 0; $y < $coinCount; $y++) {
                $nameCoin = $nameCoinCrawler->eq($y)->filter('div.deviceHeader2>span')->text();
                $workItem = (new WorkItemDTO())
                    ->setAlias($nameCoin);
                $isMbtc = false;

                if (isset($coinList[$nameCoin])) {
                    $workItem->setCoin($coinList[$nameCoin]);
                } elseif (mb_strpos(mb_convert_case($nameCoin, MB_CASE_UPPER), 'NH-') === 0) {
                    $workItem->setCoin($coinList['BTC']);
                    $isMbtc = true;
                } else {
                    throw new NotFoundException('Not found coin ' . $nameCoin);
                }

                if ($y === 0) {
                    $coinDataPosition = 0;
                    $powerConsumption = $deviceDataCrawler->eq(1)->filter('table>tr>td')->text();
                    $powerConsumption = $this->valueFilterHelper->getIntFrom(mb_strstr($powerConsumption, 'w', true));
                } else {
                    $coinDataPosition = 2;
                    $powerConsumption = 0;
                }

                $tdCrawler = $trCrawler->eq($coinDataPosition)->filter('td');
                $hashRate = $tdCrawler->eq(0)->text();
                $workItem->setHashRate($hashRate);

                $count = $tdCrawler->eq(2)->text();
                $count = $this->valueFilterHelper->getFloatFrom(mb_strstr($count, ' ', true));

                if ($count === null) {
                    $message = sprintf(
                        "gpu: %s, alias: %s, count = %s",
                        $gpu->getName(),
                        $workItem->getAlias(),
                        $tdCrawler->eq(2)->text(),
                    );
                    $this->logger->warning($message);

                    continue 2;
                }

                if ($isMbtc) {
                    $count = $count * 0.001;
                }

                $workItem->setCount($count);
                $workItem->setPowerConsumption($powerConsumption);
                $workDTO->addItem($workItem);
            }

            $worksDTO[$alias] = $workDTO;
        }

        return $worksDTO;
    }
}
