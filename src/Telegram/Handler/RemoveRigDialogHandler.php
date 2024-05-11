<?php
declare(strict_types=1);

namespace App\Telegram\Handler;

use App\Component\TelegramDialog\BaseDialogHandler;
use App\Helper\ValueFilterHelper;
use App\Service\RigService;
use Psr\Cache\InvalidArgumentException;
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Telegram\Bot\Objects\Update;

class RemoveRigDialogHandler extends BaseDialogHandler
{
    use askChoiceRigTrait;

    public function __construct(
        Api                                $bot,
        private readonly RigService        $rigService,
        private readonly ValueFilterHelper $valueFilterHelper,
    ) {
        parent::__construct($bot);
    }

    /**
     * @throws TelegramSDKException
     * @throws InvalidArgumentException
     */
    public function removeRig(Update $update): void
    {
        if ($this->hasCallbackData($update)) {
            $rigId = $this->valueFilterHelper->getIntFrom($update->callbackQuery->data);
            $this->deleteMessage($update);

            $this->rigService->removeRigById($rigId);
            $this->sendMessage('Риг удален');
        }
    }
}
