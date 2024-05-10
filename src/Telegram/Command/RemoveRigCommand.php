<?php
declare(strict_types=1);

namespace App\Telegram\Command;


use App\Component\TelegramDialog\DialogManager;
use App\Component\TelegramDialog\Exception\StorageException;
use App\Telegram\Dialog\RemoveRigDialog;
use Telegram\Bot\Commands\Command;

class RemoveRigCommand extends Command
{
    protected string $name        = 'removeRig';
    protected string $description = 'Удалить риг';

    public function __construct(
        private readonly DialogManager $dialogManager,
    ) {
    }

    /**
     * @throws StorageException
     */
    public function handle(): void
    {
        $removeRigDialog = new RemoveRigDialog($this->update->getChat()->id);
        $this->dialogManager->create($removeRigDialog);
    }
}
