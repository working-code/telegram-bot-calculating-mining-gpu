<?php

namespace App\Command;

use App\Component\TelegramDialog\DialogManager;
use App\Component\TelegramDialog\Exception\InvalidDialogStepException;
use App\Component\TelegramDialog\Exception\NotFoundHandlerException;
use App\Component\TelegramDialog\Exception\StorageException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Telegram\Bot\Api;
use Throwable;

#[AsCommand(
    name: 'telegramBot:daemon:start',
    description: 'start telegram bot',
)]
class TelegramStartAsDaemonCommand extends Command
{
    private const SLEEP_TIME = 1;
    private bool $stop = false;

    public function __construct(
        private readonly Api             $telegram,
        private readonly DialogManager   $dialogManager,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        pcntl_signal(SIGTERM, function () {
            $this->stop = true;
        });
        $pid = pcntl_fork();

        if ($pid == -1) {
            $this->logger->error('Error fork process');

            return self::FAILURE;
        } elseif ($pid) {
            $this->logger->info('parent process end');

            return self::SUCCESS;
        } else {
            try {
                $this->startTelegramBot();
            } catch (Throwable $exception) {
                $this->logger->error($exception->getMessage());

                return self::FAILURE;
            }

            return self::SUCCESS;
        }
    }

    /**
     * @throws StorageException
     * @throws InvalidDialogStepException
     * @throws NotFoundHandlerException
     */
    private
    function startTelegramBot(): void
    {
        while (!$this->stop) {
            $updates = $this->telegram->commandsHandler();

            foreach ($updates as $update) {
                if ($this->dialogManager->exists($update)) {
                    $this->dialogManager->proceed($update);
                }
            }

            sleep(self::SLEEP_TIME);
        }
    }
}
