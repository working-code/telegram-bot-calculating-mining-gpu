<?php
declare(strict_types=1);

namespace App\Telegram\Command;

use App\Exception\NotFoundException;
use App\Report\ProfitableAlgorithmsReport;
use Psr\Cache\InvalidArgumentException;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Exceptions\TelegramSDKException;

class GetProfitableAlgorithmsCommand extends Command
{
    protected string $name        = 'getProfitableAlgorithms';
    protected string $description = 'Получить 5 прибыльных алгоритмов для своих ригов';

    public function __construct(
        private readonly ProfitableAlgorithmsReport $profitableAlgorithmsReport,
    ) {
    }

    /**
     * @throws TelegramSDKException
     * @throws InvalidArgumentException
     */
    public function handle(): void
    {
        $chatId = $this->update->getChat()->id;

        try {
            $message = $this->profitableAlgorithmsReport->getReportByTelegramId($chatId);
        } catch (NotFoundException $e) {
            $message = $e->getMessage();
        }

        $this->getTelegram()->sendMessage([
            'chat_id'    => $chatId,
            'text'       => $message,
            'parse_mode' => 'Markdown',
        ]);
    }
}
