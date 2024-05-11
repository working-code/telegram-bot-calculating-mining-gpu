<?php
declare(strict_types=1);

namespace App\Telegram\Command;

use App\Exception\NotFoundException;
use App\Helper\TelegramHelper;
use App\Report\InfoByAllRigsReport;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Exceptions\TelegramSDKException;

class GetInfoByAllRigsCommand extends Command
{
    protected string $name        = 'getInfoByAllRigs';
    protected string $description = 'Получить список ригов и их настройки';

    public function __construct(
        private readonly InfoByAllRigsReport $infoByAllRigsReport,
        private readonly TelegramHelper      $telegramHelper,
    ) {
    }

    /**
     * @throws TelegramSDKException
     */
    public function handle(): void
    {
        $chatId = $this->update->getChat()->id;

        try {
            $messages = $this->infoByAllRigsReport->getReportByTelegramId($chatId);
            $this->telegramHelper->sendMessages($messages, $chatId);
        } catch (NotFoundException $e) {
            $this->getTelegram()->sendMessage([
                'chat_id' => $chatId,
                'text'    => 'Сначала создайте риг',
            ]);
        }
    }
}
