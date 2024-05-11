<?php
declare(strict_types=1);

namespace App\Report;

use App\Exception\NotFoundException;
use App\Service\RigService;

readonly class InfoByAllRigsReport
{
    public function __construct(
        private RigService $rigService,
    ) {
    }

    /**
     * @throws NotFoundException
     */
    public function getReportByTelegramId(int $telegramId): array
    {
        $rigs = $this->rigService->getRigsByTelegramId($telegramId);

        if (!$rigs) {
            throw new NotFoundException('Rigs not found');
        }

        return $this->createReport($rigs);
    }

    private function createReport(array $rigs): array
    {
        $report = [];
        $reportPart = sprintf("%s\n\n", '*Отчет по ригам.*');

        foreach ($rigs as $rig) {
            $reportPart .= sprintf(
                "\xF0\x9F\x92\xBB *%s*\nСтоимость электроэнергии: *%.2f руб*\xF0\x9F\x92\xB0 \nКПД БП: *%d%%*\nПотребление тушки: *%d ватт*\n",
                $rig->getName(),
                $rig->getElectricityCost(),
                $rig->getPowerSupplyEfficiency(),
                $rig->getMotherboardConsumption(),
            );

            if ($rig->getItems()->count() === 0) {
                $reportPart .= "Видеокарт `нет`\n";
            } else {
                $reportPart .= "*Видеокарты:*\n";

                foreach ($rig->getItems() as $item) {
                    $reportPart .= sprintf(
                        "`%s x %d`\n",
                        $item->getGpu()->getName(),
                        $item->getCount()
                    );
                }
            }

            $reportPart .= "\n";
            $report[] = $reportPart;
            $reportPart = '';
        }

        return $report;
    }
}
