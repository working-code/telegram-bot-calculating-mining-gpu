<?php
declare(strict_types=1);

namespace App\Telegram\Command;

use App\DTO\Output\CreateReportProfitableAlgorithmsDTO;
use App\Service\AsyncService;
use Symfony\Component\Serializer\SerializerInterface;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Exceptions\TelegramSDKException;

class GetProfitableAlgorithmsCommand extends Command
{
    protected string $name        = 'getProfitableAlgorithms';
    protected string $description = 'Получить пять прибыльных алгоритмов для ригов';

    public function __construct(
        private readonly AsyncService        $asyncService,
        private readonly SerializerInterface $serializer,
    ) {
    }

    /**
     * @throws TelegramSDKException
     */
    public function handle(): void
    {
        $chatId = $this->update->getChat()->id;
        $this->getTelegram()->sendMessage([
            'chat_id' => $chatId,
            'text'    => 'Идет расчет ...',
        ]);

        $this->asyncService->publishToExchange(
            AsyncService::CREATE_REPORT_PROFITABLE_ALGORITHMS,
            $this->serializer->serialize(new CreateReportProfitableAlgorithmsDTO($chatId), 'json')
        );
    }
}
