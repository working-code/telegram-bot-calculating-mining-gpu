<?php
declare(strict_types=1);

namespace App\Consumer\CreateReportSettingsForProfitableAlgorithms\Input;

use Symfony\Component\Validator\Constraints as Assert;

class Message
{
    #[Assert\Type('numeric')]
    private int $telegramId;

    #[Assert\Type('numeric')]
    private int $rigId;

    public function getTelegramId(): int
    {
        return $this->telegramId;
    }

    public function setTelegramId(int $telegramId): self
    {
        $this->telegramId = $telegramId;

        return $this;
    }

    public function getRigId(): int
    {
        return $this->rigId;
    }

    public function setRigId(int $rigId): self
    {
        $this->rigId = $rigId;

        return $this;
    }
}
