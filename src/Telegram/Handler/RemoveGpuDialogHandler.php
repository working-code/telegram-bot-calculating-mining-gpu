<?php
declare(strict_types=1);

namespace App\Telegram\Handler;

use App\Component\TelegramDialog\BaseDialogHandler;
use App\Exception\NotFoundException;
use App\Helper\ValueFilterHelper;
use App\Service\RigItemService;
use App\Service\RigService;
use App\Telegram\Dialog\RemoveGpuDialog;
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Objects\Update;

class RemoveGpuDialogHandler extends BaseDialogHandler
{
    use askChoiceRigTrait;

    public function __construct(
        Api                                $bot,
        private readonly RigService        $rigService,
        private readonly ValueFilterHelper $valueFilterHelper,
        private readonly RigItemService    $rigItemService,
    ) {
        parent::__construct($bot);
    }

    /**
     * @throws TelegramSDKException
     */
    public function saveRig(Update $update): void
    {
        if ($this->hasCallbackData($update)) {
            $rigId = $this->valueFilterHelper->getIntFrom($update->callbackQuery->data);
            $this->getDialog()->setRigId($rigId);
            $this->deleteMessage($update);

            $this->askChoiceGpu();
        }
    }

    public function getDialog(): RemoveGpuDialog
    {
        return $this->dialog;
    }

    /**
     * @throws TelegramSDKException
     */
    private function askChoiceGpu(): void
    {
        if (
            !$this->getDialog()->getKeyboardGpuData()
            && !$this->getDialog()->getRigItemIds()
        ) {
            try {
                $rig = $this->rigService->getRigWithRigItemAndGpu($this->getDialog()->getRigId());
            } catch (NotFoundException $e) {
                $this->transition('checkResponseRemoveGpu');
                $this->sendMessage('В риге нет карт');

                return;
            }

            foreach ($rig->getItems() as $rigItem) {
                $this->getDialog()->addItemKeyboardGpuData(
                    sprintf('%s * %d', $rigItem->getGpu()->getName(), $rigItem->getCount()),
                    $rigItem->getId(),
                );
            }
        }

        $reply_markup = $this->getInlineKeyboard();

        foreach ($this->getDialog()->getKeyboardGpuData() as $keyValue => $keyName) {
            $reply_markup->row([Keyboard::inlineButton(['text' => $keyName, 'callback_data' => $keyValue])]);
        }

        $this->sendMessage('Выберите видеокарту', $reply_markup);
    }

    /**
     * @throws TelegramSDKException
     */
    public function askRemoveGpu(Update $update): void
    {
        if ($this->hasCallbackData($update)) {
            $rigItemId = $this->valueFilterHelper->getIntFrom($update->callbackQuery->data);
            $this->getDialog()->addRigItemId($rigItemId);
            $this->getDialog()->removeItemKeyboardGpuData($rigItemId);

            $this->deleteMessage($update);

            if ($this->getDialog()->getKeyboardGpuData()) {
                $reply_markup = ($this->getInlineKeyboard())
                    ->row([
                        Keyboard::inlineButton(['text' => 'Да', 'callback_data' => 'yes']),
                        Keyboard::inlineButton(['text' => 'Нет', 'callback_data' => 'no']),
                    ]);
                $this->sendMessage('Удалить еще карту?', $reply_markup);
            } else {
                $this->removeGpu();
            }
        }
    }

    /**
     * @throws TelegramSDKException
     */
    private function removeGpu(): void
    {
        $this->rigItemService->removeRigItemByIds($this->getDialog()->getRigItemIds());
        $this->sendMessage('Карты удалены');
    }

    /**
     * @throws TelegramSDKException
     */
    public function checkResponseRemoveGpu(Update $update): void
    {
        if ($this->hasCallbackData($update)) {
            if ($update->callbackQuery->data === 'yes') {
                $this->askChoiceGpu();
                $this->transition('askRemoveGpu');
            } else {
                $this->transition('checkResponseRemoveGpu');
                $this->removeGpu();
            }

            $this->deleteMessage($update);
        }
    }
}
