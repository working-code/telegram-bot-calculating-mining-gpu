<?php
declare(strict_types=1);

namespace App\Telegram\Command;

use App\Component\TelegramDialog\DialogManager;
use App\Component\TelegramDialog\Exception\StorageException;
use App\Telegram\Dialog\AddRigDialog;
use Telegram\Bot\Commands\Command;

class AddRigCommand extends Command
{
    protected string $name        = 'addRig';
    protected string $description = 'Добавить риг';

    public function __construct(
        private readonly DialogManager $dialogManager,
    ) {
    }

    /**
     * @throws StorageException
     */
    public function handle(): void
    {
        $addRigDialog = (new AddRigDialog($this->update->getChat()->id));
        $this->dialogManager->create($addRigDialog);
    }
}
