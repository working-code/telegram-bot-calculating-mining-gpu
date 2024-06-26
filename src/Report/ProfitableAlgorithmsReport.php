<?php
declare(strict_types=1);

namespace App\Report;

use App\Entity\Rig;
use App\Exception\NotFoundException;
use App\Service\RigService;
use Psr\Cache\InvalidArgumentException;

readonly class ProfitableAlgorithmsReport
{
    public function __construct(
        private RigService $rigService,
    ) {
    }

    /**
     * @throws NotFoundException
     * @throws InvalidArgumentException
     */
    public function getReportByTelegramId(int $telegramId): array
    {
        $rigs = $this->rigService->getRigsByTelegramId($telegramId);

        if (!$rigs) {
            throw new NotFoundException('Сначала создайте риг');
        }

        $report = [];
        $reportPart = "Для каждого алгоритма указан доход в сутки c указанного количества карт\n\n";

        foreach ($rigs as $rig) {
            if (!$rig->hasItems()) {
                continue;
            }

            $calculationByRig = $this->rigService->getCalculationByRig($rig);
            $reportPart .= $this->createReport($calculationByRig, $rig);
            $report[] = $reportPart;
            $reportPart = '';
        }

        if (!$report) {
            throw new NotFoundException('У вас нет карт, добавьте карты в риг');
        }

        return $report;
    }

    private function createReport(array $calculationByRig, Rig $rig): string
    {
        $report = sprintf(
            "\xF0\x9F\x92\xBB *%s:*\n",
            $rig->getName(),
        );

        foreach ($calculationByRig['items'] as $item) {
            $report .= sprintf(
                "\n*%s x %d*\n`",
                $item['gpuName'],
                $item['gpuCount'],
            );

            foreach ($item['works'] as $workIndex => $workData) {
                $report .= sprintf(
                    "%d) %s %.2f$ / %.2f руб\n",
                    $workIndex + 1,
                    $workData['work']->getAlias(),
                    $workData['profitInUsd'],
                    $workData['profitInRub'],
                );
            }

            $report .= '`';
        }

        $report .= "\n\xF0\x9F\x92\xB0 _Доход в моменте за день:_\n`";

        foreach ($calculationByRig['totalByWorks'] as $workIndex => $workData) {
            $report .= sprintf(
                "%d) %.2f$ / %.2f руб\n",
                $workIndex + 1,
                $workData['profitPerDayInUsd'],
                $workData['profitPerDayInRub'],
            );
        }

        $report .= "`\n\xF0\x9F\x92\xB0 _Доход в моменте за 30 дней:_\n`";

        foreach ($calculationByRig['totalByWorks'] as $workIndex => $workData) {
            $report .= sprintf(
                "%d) %.2f$ / %.2f руб\n",
                $workIndex + 1,
                $workData['profitPerMonthInUsd'],
                $workData['profitPerMonthInRub'],
            );
        }

        $report .= "`\n\xF0\x9F\x92\xB0 _Доход в моменте за год:_\n`";

        foreach ($calculationByRig['totalByWorks'] as $workIndex => $workData) {
            $report .= sprintf(
                "%d) %.2f$ / %.2f руб\n",
                $workIndex + 1,
                $workData['profitPerYearInUsd'],
                $workData['profitPerYearInRub'],
            );
        }

        $report .= "`\n\n";

        return $report;
    }
}
