<?php
declare(strict_types=1);

namespace App\DTO\Output;

readonly class CreateReportSettingsForProfitableAlgorithmsDTO
{
    public function __construct(
        private int $rigId,
        private int $telegramId
    ) {
    }

    public function getRigId(): int
    {
        return $this->rigId;
    }

    public function getTelegramId(): int
    {
        return $this->telegramId;
    }
}
