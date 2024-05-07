<?php
declare(strict_types=1);

namespace App\Telegram\Command;

use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

class StartCommand extends Command
{
    protected string $name        = 'help';
    protected array  $aliases     = ['start'];
    protected string $description = 'Список доступных команд';

    public function handle(): void
    {
        $fallbackUsername = $this->getUpdate()->getMessage()->from->username;
        $username = $this->argument('username', $fallbackUsername);

        $this->replyWithMessage([
            'text' => "Привет {$username}! Список доступных команд:",
        ]);

        $this->replyWithChatAction(['action' => Actions::TYPING]);

        $commands = $this->getTelegram()->getCommands();
        $response = '';

        foreach ($commands as $name => $command) {
            $response .= sprintf('/%s - %s' . PHP_EOL, $name, $command->getDescription());
        }

        $this->replyWithMessage(['text' => $response]);
    }
}
