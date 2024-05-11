<?php
declare(strict_types=1);

namespace App\Consumer\CreateReportProfitableAlgorithms\Input;

use Symfony\Component\Validator\Constraints as Assert;

class Message
{
    #[Assert\Type('numeric')]
    private int $telegramId;

    public function getTelegramId(): int
    {
        return $this->telegramId;
    }

    public function setTelegramId(int $telegramId): self
    {
        $this->telegramId = $telegramId;

        return $this;
    }
}
