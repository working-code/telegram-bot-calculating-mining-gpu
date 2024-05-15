<?php
declare(strict_types=1);

namespace App\Service;

use App\DTO\GpuDTO;
use App\DTO\RigDTO;
use App\DTO\UserDTO;
use App\Entity\Rig;
use App\Exception\NotFoundException;
use App\Exception\ValidationErrorException;
use App\Manager\RigItemManager;
use App\Manager\RigManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

readonly class RigService
{
    private const  COUNT_OF_WATTS_IN_KW          = 1000;
    private const  COUNT_OF_DAY_IN_MONTH         = 30;
    private const  COUNT_OF_DAY_IN_YEAR          = 365;
    private const  COUNT_OF_PROFITABLE_ALGORITHM = 5;
    private const  COUNT_HOURS_IN_DAY            = 24;

    public function __construct(
        private RigManager             $rigManager,
        private UserService            $userService,
        private ValidatorInterface     $validator,
        private EntityManagerInterface $em,
        private GpuService             $gpuService,
        private RigItemManager         $rigItemManager,
        private WorkService            $workService,
        private CurrencyService        $currencyService,
        private TagAwareCacheInterface $cache,
    ) {
    }

    /**
     * @throws NotFoundException
     */
    public function getRigWithRigItemAndGpu(int $rigId): Rig
    {
        $rigRepository = $this->em->getRepository(Rig::class);
        $rig = $rigRepository->getRigWithRigItemAndGpu($rigId);

        if ($rig === null) {
            throw new NotFoundException('Риг не найден');
        }

        return $rig;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getCalculationByRig(Rig $rig): array
    {
        return $this->cache->get(
            Cache::CALCULATION_BY_RIG_KEY . $rig->getId(),
            function (ItemInterface $item) use ($rig): array {
                $item->tag(Cache::CACHE_TAG_CALCULATION_BY_RIG);

                return $this->getCalculationForRig($rig);
            });
    }

    /**
     * @throws InvalidArgumentException
     */
    private function getCalculationForRig(Rig $rig): array
    {
        $electricityCost = $this->currencyService->convertRubInUsd($rig->getElectricityCost());
        $efficiency = 100 / $rig->getPowerSupplyEfficiency();
        $items = [];
        $totalRows = [];

        foreach ($rig->getItems() as $rigItem) {
            $works = $this->workService->getWorksWithItemsAndCoinByGpu($rigItem->getGpu());
            $workList = [];

            foreach ($works as $work) {
                $workRevenue = 0;
                $workPowerConsumption = 0;

                foreach ($work->getItems() as $item) {
                    $workRevenue += $item->getCoin()->getPrice() * $item->getCount() * $rigItem->getCount();
                    $workPowerConsumption += $item->getPowerConsumption() * $rigItem->getCount();
                }

                $profit = $workRevenue - $workPowerConsumption * self::COUNT_HOURS_IN_DAY
                    / self::COUNT_OF_WATTS_IN_KW * $efficiency * $electricityCost;

                $workList[] = [
                    'profitInUsd' => $profit,
                    'profitInRub' => $this->currencyService->convertUsdInRub($profit),
                    'work'        => $work,
                ];
            }

            usort($workList, function (array $a, array $b) {
                return $b['profitInUsd'] <=> $a['profitInUsd'];
            });
            $workList = array_slice($workList, 0, self::COUNT_OF_PROFITABLE_ALGORITHM);

            $items[] = [
                'gpuName'  => $rigItem->getGpu()->getName(),
                'gpuCount' => $rigItem->getCount(),
                'works'    => $workList,
            ];

            //calculation profit by works for rig
            foreach ($workList as $workIndex => $itemData) {
                if (!isset($totalRows[$workIndex])) {
                    $totalRows[$workIndex]['profitPerDayInUsd'] = -$rig->getMotherboardConsumption()
                        * self::COUNT_HOURS_IN_DAY / self::COUNT_OF_WATTS_IN_KW * $efficiency * $electricityCost;
                }

                $totalRows[$workIndex]['profitPerDayInUsd'] += $itemData['profitInUsd'];
            }

            foreach ($totalRows as $rowIndex => $row) {
                $totalRows[$rowIndex]['profitPerDayInRub'] = $this->currencyService->convertUsdInRub(
                    $row['profitPerDayInUsd']
                );

                $totalRows[$rowIndex]['profitPerMonthInUsd'] = $row['profitPerDayInUsd'] * self::COUNT_OF_DAY_IN_MONTH;
                $totalRows[$rowIndex]['profitPerMonthInRub'] = $this->currencyService->convertUsdInRub(
                    $totalRows[$rowIndex]['profitPerMonthInUsd']
                );

                $totalRows[$rowIndex]['profitPerYearInUsd'] = $row['profitPerDayInUsd'] * self::COUNT_OF_DAY_IN_YEAR;
                $totalRows[$rowIndex]['profitPerYearInRub'] = $this->currencyService->convertUsdInRub(
                    $totalRows[$rowIndex]['profitPerYearInUsd']
                );
            }
        }

        return [
            'totalByWorks' => $totalRows,
            'items'        => $items,
        ];
    }

    /**
     * @return Rig[]
     */
    public function getRigsByTelegramId(int $telegramId): array
    {
        $rigRepository = $this->em->getRepository(Rig::class);

        return $rigRepository->findByTelegramId($telegramId);
    }

    /**
     * @throws ValidationErrorException
     */
    public function createRigBy(RigDTO $rigDTO, UserDTO $userDTO): void
    {
        $user = $this->userService->findUserByTelegramId($userDTO->getId());

        if (!$user) {
            $user = $this->userService->createUserByUserDTO($userDTO);
        }

        $rig = $this->rigManager->create(
            $rigDTO->getName(),
            $rigDTO->getElectricityCost(),
            $rigDTO->getPowerSupplyEfficiency(),
            $rigDTO->getMotherboardConsumption()
        );
        $rig->setUser($user);
        $user->addRig($rig);

        $errors = $this->validator->validate($rig);

        if ($errors->count()) {
            throw new ValidationErrorException($errors);
        }

        $this->rigManager->emFlush();
    }

    /**
     * @param GpuDTO[] $gpuList
     *
     * @throws NotFoundException
     * @throws InvalidArgumentException
     */
    public function addListGpuInRig(array $gpuList, int $rigId): void
    {
        $rig = $this->getRigById($rigId);

        if (!$rig) {
            throw new NotFoundException(sprintf('Rig not found with id "%s"', $rigId));
        }

        $gpuIds = array_map(static fn (GpuDTO $gpuDTO) => $gpuDTO->getId(), $gpuList);
        $gpuListCount = array_map(static fn (GpuDTO $gpuDTO) => $gpuDTO->getCount(), $gpuList);
        $gpuListCount = array_combine($gpuIds, $gpuListCount);
        $gpuEntityGpuList = $this->gpuService->getListGpuByIds($gpuIds);

        foreach ($gpuEntityGpuList as $gpu) {
            $this->rigItemManager->create($rig, $gpu, $gpuListCount[$gpu->getId()]);
        }

        $this->rigItemManager->emFlush();
        $this->removeCalculationByRigFromCache($rigId);
    }

    public function getRigById(int $rigId): ?Rig
    {
        $rigRepository = $this->em->getRepository(Rig::class);

        return $rigRepository->find($rigId);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function removeCalculationByRigFromCache(int $rigId): void
    {
        $this->cache->delete(Cache::CALCULATION_BY_RIG_KEY . $rigId);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function removeRigById(int $rigId): void
    {
        $rig = $this->getRigById($rigId);
        $this->rigManager->remove($rig);
        $this->rigManager->emFlush();

        $this->removeCalculationByRigFromCache($rigId);
    }
}
