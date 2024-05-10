<?php
declare(strict_types=1);

namespace App\Telegram\Command;

use App\Component\TelegramDialog\DialogManager;
use App\Component\TelegramDialog\Exception\StorageException;
use App\Telegram\Dialog\GetSettingsForProfitableAlgorithmsDialog;
use Telegram\Bot\Commands\Command;

class GetSettingsForProfitableAlgorithmsCommand extends Command
{
    protected string $name        = 'getSettingsForProfitableAlgorithms';
    protected string $description = 'Получить настройки для карт по 5-ти прибыльным алгоритмам';

    public function __construct(
        private readonly DialogManager $dialogManager,
    ) {
    }

    /**
     * @throws StorageException
     */
    public function handle(): void
    {
        $settingsForAlgorithmsDialog = new GetSettingsForProfitableAlgorithmsDialog($this->update->getChat()->id);
        $this->dialogManager->create($settingsForAlgorithmsDialog);
    }
}
