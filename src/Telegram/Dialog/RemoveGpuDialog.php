<?php
declare(strict_types=1);

namespace App\Telegram\Dialog;

use App\Component\TelegramDialog\BaseDialog;
use App\Telegram\Handler\RemoveGpuDialogHandler;

class RemoveGpuDialog extends BaseDialog
{
    protected array  $steps        = ['askChoiceRig', 'saveRig', 'askRemoveGpu', 'checkResponseRemoveGpu'];
    protected string $handlerClass = RemoveGpuDialogHandler::class;

    private int   $rigId;
    private array $rigItemIds      = [];
    private array $keyboardGpuData = [];

    public function addItemKeyboardGpuData(string $keyName, int $keyValue): void
    {
        $this->keyboardGpuData[$keyValue] = $keyName;
    }

    public function removeItemKeyboardGpuData(int $value): void
    {
        if (isset($this->keyboardGpuData[$value])) {
            unset($this->keyboardGpuData[$value]);
        }
    }

    public function getKeyboardGpuData(): array
    {
        return $this->keyboardGpuData;
    }

    public function addRigItemId(int $rigItemId): void
    {
        $this->rigItemIds[] = $rigItemId;
    }

    public function getRigItemIds(): array
    {
        return $this->rigItemIds;
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
