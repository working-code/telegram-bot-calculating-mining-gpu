<?php
declare(strict_types=1);

namespace App\Component\TelegramDialog;

use  App\Component\TelegramDialog\Exception\InvalidDialogStepException;
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Objects\Update;

abstract class BaseDialogHandler implements DialogHandlerInterface
{
    protected BaseDialog $dialog;
    private ?int         $manualTransitionIndex = null;

    public function __construct(protected Api $bot,)
    {
    }

    protected function getInlineKeyboard(): Keyboard
    {
        return Keyboard::make()
            ->inline()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true);
    }

    /**
     * @throws InvalidDialogStepException
     */
    public function proceed(Update $update): void
    {
        if ($this->isStart()) {
            $this->beforeAllStep($update);
        }

        if ($this->isEnd()) {
            $this->afterAllStep($update);

            return;
        }

        $this->checkExistCurrentStep();
        $stepMethodName   = $this->getMethodNameForCurrentStep();
        $currentStepIndex = $this->dialog->getNextStepIndex();

        $this->beforeEveryStep($update, $currentStepIndex);
        $this->$stepMethodName($update);
        $this->afterEveryStep($update, $currentStepIndex);

        if ($this->hasManualTransition()) {
            $this->getDialog()->setNextStepIndex($this->manualTransitionIndex);
            $this->manualTransitionIndex = null;
        } else {
            $this->dialog->incrementNextStepIndex();
        }
    }

    public function isStart(): bool
    {
        return $this->dialog->getNextStepIndex() === 0;
    }

    protected function beforeAllStep(Update $update): void
    {
    }

    public function isEnd(): bool
    {
        return $this->dialog->getNextStepIndex() >= count($this->getDialog()->getSteps());
    }

    public function getDialog(): BaseDialog
    {
        return $this->dialog;
    }

    public function setDialog(BaseDialog $dialog): self
    {
        $this->dialog = $dialog;

        return $this;
    }

    protected function afterAllStep(Update $update): void
    {
    }

    /**
     * @throws InvalidDialogStepException
     */
    private function checkExistCurrentStep(): void
    {
        if (!array_key_exists($this->dialog->getNextStepIndex(), $this->getDialog()->getSteps())) {
            throw new InvalidDialogStepException("Undefined step with index $this->dialog->getNextStepIndex().");
        }
    }

    /**
     * @throws InvalidDialogStepException
     */
    private function getMethodNameForCurrentStep(): string
    {
        $stepName = $this->dialog->getSteps()[$this->dialog->getNextStepIndex()];

        if (is_string($stepName)) {
            $stepMethodName = $stepName;

            if (!method_exists($this, $stepMethodName)) {
                throw new InvalidDialogStepException(
                    sprintf('Public method “%s::%s()” is not available.', $this::class, $stepMethodName)
                );
            }

            return $stepMethodName;
        }

        throw new InvalidDialogStepException('step type not string.');
    }

    protected function beforeEveryStep(Update $update, int $step): void
    {
    }

    protected function afterEveryStep(Update $update, int $step): void
    {
    }

    private function hasManualTransition(): bool
    {
        return $this->manualTransitionIndex !== null;
    }

    public function end(): void
    {
        $this->dialog->setNextStepIndex(count($this->dialog->getSteps()));
    }

    /**
     * @throws TelegramSDKException
     */
    protected function sendMessage(string $message, $reply_markup = null): void
    {
        $options = [
            'chat_id' => $this->dialog->getChatId(),
            'text'    => $message,
        ];

        if ($reply_markup) {
            $options['reply_markup'] = $reply_markup;
        }

        $this->bot->sendMessage($options);
    }

    /**
     * @throws TelegramSDKException
     */
    protected function deleteMessage(Update $update): void
    {
        $messageId = null;

        if ($this->hasCallbackData($update)) {
            $messageId = $update->callbackQuery->message->messageId;
        } elseif ($update->message?->messageId) {
            $messageId = $update->message?->messageId;
        }

        if ($messageId) {
            $this->bot->deleteMessage([
                'chat_id'    => $this->dialog->getChatId(),
                'message_id' => $messageId,
            ]);
        }
    }

    protected function hasCallbackData(Update $update): bool
    {
        return $update->callbackQuery
            && isset($update->callbackQuery->data);
    }

    protected function transition(string $stepName): void
    {
        foreach ($this->dialog->getSteps() as $index => $value) {
            if ($value === $stepName) {
                $this->manualTransitionIndex = $index;

                break;
            }
        }
    }
}
