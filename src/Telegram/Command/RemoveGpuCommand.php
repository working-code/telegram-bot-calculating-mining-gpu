<?php
declare(strict_types=1);

namespace App\Telegram\Command;

use App\Component\TelegramDialog\DialogManager;
use App\Component\TelegramDialog\Exception\StorageException;
use App\Telegram\Dialog\RemoveGpuDialog;
use Telegram\Bot\Commands\Command;

class RemoveGpuCommand extends Command
{
    protected string $name        = 'removeGpu';
    protected string $description = 'Удалить карты из рига';

    public function __construct(
        private readonly DialogManager $dialogManager,
    ) {
    }

    /**
     * @throws StorageException
     */
    public function handle(): void
    {
        $removeGpuDialog = new RemoveGpuDialog($this->update->getChat()->id);
        $this->dialogManager->create($removeGpuDialog);
    }
}
