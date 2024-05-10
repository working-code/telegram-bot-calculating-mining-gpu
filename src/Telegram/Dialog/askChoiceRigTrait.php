<?php

namespace App\Telegram\Dialog;

use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Objects\Update;

trait askChoiceRigTrait
{
    public function askChoiceRig(Update $update): void
    {
        $rigs = $this->rigService->getRigsByTelegramId($update->message?->chat?->id);

        if ($rigs) {
            $reply_markup = $this->getInlineKeyboard();

            foreach ($rigs as $rig) {
                $reply_markup->row([
                    Keyboard::inlineButton(['text' => $rig->getName(), 'callback_data' => $rig->getId()]),
                ]);
            }

            $this->sendMessage('Выберите риг', $reply_markup);
        } else {
            $this->sendMessage('Сначала создайте риг');
            $this->end();
        }
    }
}
