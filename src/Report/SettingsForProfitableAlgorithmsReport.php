<?php
declare(strict_types=1);

namespace App\Report;

use App\Entity\Rig;
use App\Entity\Work;
use App\Exception\NotFoundException;
use App\Service\RigService;

readonly class SettingsForProfitableAlgorithmsReport
{
    public function __construct(
        private RigService $rigService
    ) {
    }

    /**
     * @throws NotFoundException
     */
    public function getReportByRigId(int $rigId): array
    {
        $rig = $this->rigService->getRigById($rigId);

        if (!$rig) {
            throw new NotFoundException('Сначала создайте риг');
        }

        $calculationByRig = $this->rigService->getCalculationByRig($rig);

        return $this->createReport($calculationByRig, $rig);
    }

    private function createReport(array $calculationByRig, Rig $rig): array
    {
        $reportPart = sprintf(
            "\xF0\x9F\x92\xBB %s:\n\n%s\n",
            $rig->getName(),
            "Рекомендуемые настройки карт"
        );
        $report = [];

        foreach ($calculationByRig['items'] as $item) {
            $reportPart .= sprintf(
                "\n%s",
                $item['gpuName'],
            );

            foreach ($item['works'] as $workIndex => $workData) {
                /** @var Work $work */
                $work = $workData['work'];
                $reportPart .= sprintf(
                    "\n%d) *%s*\n",
                    $workIndex + 1,
                    $work->getAlias(),
                );

                $reportPart .= $this->getOverclockSettings($work);
                $reportPart .= $this->getItems($work);

                foreach ($work->getMinerSettings() as $minerName => $command) {
                    $reportPart .= sprintf("%s: `%s`\n", $minerName, $command);
                }
            }

            $report[] = $reportPart;
            $reportPart = '';
        }

        return $report;
    }

    private function getOverclockSettings(Work $work): string
    {
        $reportPart = '';

        foreach ($work->getOverclockSettings() as $settingName => $settingValue) {
            if (
                strcasecmp($settingName, 'Advanced') === 0
                && strcasecmp($settingValue, '') === 0
            ) {
                continue;
            } elseif (strcasecmp($settingValue, '') === 0) {
                $settingValue = '-';
            }

            $reportPart .= sprintf("`%s: %s`\n", $settingName, $settingValue);
        }

        return $reportPart;
    }

    private function getItems(Work $work): string
    {
        $reportPart = "\nРезультат:\n";

        foreach ($work->getItems() as $item) {
            $reportPart .= sprintf(
                "*%f* %s\nhash rate: *%s*\n",
                $item->getCount(),
                $item->getCoin()->getAlias(),
                $item->getHashRate(),
            );

            if ($item->getPowerConsumption() > 0) {
                $reportPart .= sprintf(
                    "Фактическое потребление: *%dW*\n",
                    $item->getPowerConsumption(),
                );
            }

            $reportPart .= "\n";
        }

        return $reportPart;
    }
}
