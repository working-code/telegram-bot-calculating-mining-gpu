<?php
declare(strict_types=1);

namespace App\Telegram\Handler;

use App\Component\TelegramDialog\BaseDialogHandler;
use App\Exception\NotFoundException;
use App\Helper\ValueFilterHelper;
use App\Report\SettingsForProfitableAlgorithmsReport;
use App\Service\RigService;
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Telegram\Bot\Objects\Update;

class GetSettingsForProfitableAlgorithmsDialogHandler extends BaseDialogHandler
{
    use askChoiceRigTrait;

    public function __construct(
        Api                                                    $bot,
        private readonly RigService                            $rigService,
        private readonly ValueFilterHelper                     $valueFilterHelper,
        private readonly SettingsForProfitableAlgorithmsReport $settingsForProfitableAlgorithmsReport,
    ) {
        parent::__construct($bot);
    }

    /**
     * @throws NotFoundException
     * @throws TelegramSDKException
     */
    public function createReport(Update $update): void
    {
        if ($this->hasCallbackData($update)) {
            $rigId = $this->valueFilterHelper->getIntFrom($update->callbackQuery->data);
            $this->deleteMessage($update);

            $messages = $this->settingsForProfitableAlgorithmsReport->getReportByRigId($rigId);

            foreach ($messages as $message) {
                $this->bot->sendMessage([
                    'chat_id'    => $this->dialog->getChatId(),
                    'text'       => $message,
                    'parse_mode' => 'Markdown',
                ]);
            }
        }
    }
}
