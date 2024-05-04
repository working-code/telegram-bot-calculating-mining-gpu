<?php
declare(strict_types=1);

namespace App\Component\TelegramDialog;

use App\Component\TelegramDialog\Exception\InvalidDialogStepException;
use App\Component\TelegramDialog\Exception\NotFoundHandlerException;
use App\Component\TelegramDialog\Exception\StorageException;
use IteratorAggregate;
use Telegram\Bot\Objects\Message;
use Telegram\Bot\Objects\Update;

class DialogManager
{
    private array $handlers = [];

    public function __construct(
        IteratorAggregate        $dialogHandlers,
        private readonly Storage $storage
    ) {
        foreach ($dialogHandlers as $dialogHandler) {
            /** @var DialogHandlerInterface $dialogHandler * */
            $this->handlers[$dialogHandler::class] = $dialogHandler;
        }
    }

    /**
     * @throws StorageException
     */
    public function create(BaseDialog $dialog): void
    {
        $this->storeDialogState($dialog);
    }

    /**
     * @throws StorageException
     */
    private function storeDialogState(BaseDialog $dialog): void
    {
        $this->storage->set($dialog->getChatId(), $dialog, $dialog::TTL);
    }

    /**
     * @throws StorageException
     * @throws InvalidDialogStepException
     * @throws NotFoundHandlerException
     */
    public function proceed(Update $update): void
    {
        $dialogHandler = $this->getDialogHandler($update);
        if ($dialogHandler === null) {
            return;
        }

        $dialogHandler->proceed($update);

        if ($dialogHandler->isEnd()) {
            $this->storage->delete($dialogHandler->getDialog()->getChatId());
            $dialogHandler->proceed($update);
        } else {
            $this->storeDialogState($dialogHandler->getDialog());
        }
    }

    /**
     * @throws StorageException
     * @throws NotFoundHandlerException
     */
    private function getDialogHandler(Update $update): ?BaseDialogHandler
    {
        if (!$this->exists($update)) {
            return null;
        }

        $message = $update->getMessage();
        assert($message instanceof Message);

        /** @var BaseDialog $dialog * */
        $dialog  = $this->storage->get($message->chat->id);
        $handler = $this->getHandler($dialog->getHandlerClass());
        /** @var BaseDialogHandler $handelr * */
        $handler->setDialog($dialog);

        return $handler;
    }

    public function exists(Update $update): bool
    {
        $message = $update->getMessage();
        $chatId  = $message instanceof Message ? $message->chat->id : null;

        return $chatId
            && $this->storage->has($chatId);
    }

    /**
     * @throws NotFoundHandlerException
     */
    private function getHandler(string $handlerClass): BaseDialogHandler
    {
        if (isset($this->handlers[$handlerClass])) {
            return $this->handlers[$handlerClass];
        }

        throw new NotFoundHandlerException(sprintf("not found handler dialog for '%s'", $handlerClass));
    }
}
