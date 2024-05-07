<?php
declare(strict_types=1);

namespace App\Telegram\Command;

use App\Component\TelegramDialog\DialogManager;
use App\Component\TelegramDialog\Exception\StorageException;
use App\Telegram\Dialog\AddGpuDialog;
use Telegram\Bot\Commands\Command;

class AddGpuCommand extends Command
{
    protected string $name        = 'addGpu';
    protected string $description = 'Добавить карты в риг';

    public function __construct(
        private readonly DialogManager $dialogManager,
    ) {
    }

    /**
     * @throws StorageException
     */
    public function handle(): void
    {
        $addGpuDialog = (new AddGpuDialog($this->update->getChat()->id));
        $this->dialogManager->create($addGpuDialog);
    }
}
