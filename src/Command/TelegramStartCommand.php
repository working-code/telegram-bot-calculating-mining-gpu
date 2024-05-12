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
    name: 'telegramBot:start',
    description: 'start telegram bot',
)]
class TelegramStartCommand extends Command
{
    private const SLEEP_TIME   = 1;
    private const JOB_MIN_TIME = 7 * 60 * 60;

    public function __construct(
        private readonly Api             $telegram,
        private readonly DialogManager   $dialogManager,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->startTelegramBot();
        } catch (Throwable $exception) {
            $this->logger->error($exception->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * @throws StorageException
     * @throws InvalidDialogStepException
     * @throws NotFoundHandlerException
     */
    private function startTelegramBot(): void
    {
        $stepCounter = 0;

        while ($stepCounter <= self::JOB_MIN_TIME) {
            $updates = $this->telegram->commandsHandler();

            foreach ($updates as $update) {
                if ($this->dialogManager->exists($update)) {
                    $this->dialogManager->proceed($update);
                }
            }

            sleep(self::SLEEP_TIME);
            $stepCounter++;
        }
    }
}
