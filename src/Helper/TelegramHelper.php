<?php
declare(strict_types=1);

namespace App\Helper;

use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramSDKException;

readonly class TelegramHelper
{
    public function __construct(
        private Api $telegram,
    ) {
    }

    /**
     * @param string[] $messages
     *
     * @throws TelegramSDKException
     */
    public function sendMessages(array $messages, int $chatId, $isMarkdown = true): void
    {
        foreach ($messages as $message) {
            $params = [
                'chat_id' => $chatId,
                'text'    => $message,
            ];

            if ($isMarkdown) {
                $params['parse_mode'] = 'Markdown';
            }

            $this->telegram->sendMessage($params);

            sleep(1);
        }
    }
}
