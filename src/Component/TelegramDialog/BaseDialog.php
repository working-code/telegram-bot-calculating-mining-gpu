<?php
declare(strict_types=1);

namespace App\Component\TelegramDialog;

abstract class BaseDialog
{
    public const TTL = 300;
    protected array $steps;
    protected string $handlerClass;
    private int     $chatId;
    private int     $nextStepIndex = 0;

    public function __construct(int $chatId)
    {
        $this->chatId = $chatId;
    }

    public function getHandlerClass(): string
    {
        return $this->handlerClass;
    }

    public function getChatId(): int
    {
        return $this->chatId;
    }

    public function getSteps(): array
    {
        return $this->steps;
    }

    public function getNextStepIndex(): int
    {
        return $this->nextStepIndex;
    }

    public function setNextStepIndex(int $nextStepIndex): self
    {
        $this->nextStepIndex = $nextStepIndex;

        return $this;
    }

    public function incrementNextStepIndex(): self
    {
        ++$this->nextStepIndex;

        return $this;
    }
}
