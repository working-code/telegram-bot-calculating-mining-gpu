<?php
declare(strict_types=1);

namespace App\Telegram\Handler;

use App\Component\TelegramDialog\BaseDialogHandler;
use App\DTO\Output\CreateReportSettingsForProfitableAlgorithmsDTO;
use App\Helper\ValueFilterHelper;
use App\Service\AsyncService;
use App\Service\RigService;
use Symfony\Component\Serializer\SerializerInterface;
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Telegram\Bot\Objects\Update;

class GetSettingsForProfitableAlgorithmsDialogHandler extends BaseDialogHandler
{
    use askChoiceRigTrait;

    public function __construct(
        Api                                  $bot,
        private readonly RigService          $rigService,
        private readonly ValueFilterHelper   $valueFilterHelper,
        private readonly AsyncService        $asyncService,
        private readonly SerializerInterface $serializer,
    ) {
        parent::__construct($bot);
    }

    /**
     * @throws TelegramSDKException
     */
    public function createReport(Update $update): void
    {
        if ($this->hasCallbackData($update)) {
            $rigId = $this->valueFilterHelper->getIntFrom($update->callbackQuery->data);
            $this->deleteMessage($update);
            $this->sendMessage('Идет расчет ...');

            $createReportSettingsForProfitableAlgorithmsDTO = new CreateReportSettingsForProfitableAlgorithmsDTO(
                $rigId,
                $this->dialog->getChatId()
            );

            $this->asyncService->publishToExchange(
                AsyncService::CREATE_REPORT_SETTINGS_FOR_PROFITABLE_ALGORITHMS,
                $this->serializer->serialize($createReportSettingsForProfitableAlgorithmsDTO, 'json')
            );
        }
    }
}
