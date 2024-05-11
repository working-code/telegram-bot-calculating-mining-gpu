<?php
declare(strict_types=1);

namespace App\DTO\Output;

readonly class CreateReportProfitableAlgorithmsDTO
{
    public function __construct(private int $telegramId)
    {
    }

    public function getTelegramId(): int
    {
        return $this->telegramId;
    }
}
