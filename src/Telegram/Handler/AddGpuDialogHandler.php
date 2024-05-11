<?php
declare(strict_types=1);

namespace App\Telegram\Handler;

use App\Component\TelegramDialog\BaseDialogHandler;
use App\DTO\GpuDTO;
use App\Entity\Enum\GpuBrand;
use App\Exception\NotFoundException;
use App\Helper\ValidationHelper;
use App\Helper\ValueFilterHelper;
use App\Service\GpuService;
use App\Service\RigService;
use App\Telegram\Dialog\AddGpuDialog;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Objects\Update;

class AddGpuDialogHandler extends BaseDialogHandler
{
    use askChoiceRigTrait;

    public function __construct(
        Api                                 $bot,
        private readonly RigService         $rigService,
        private readonly ValueFilterHelper  $valueFilterHelper,
        private readonly ValidatorInterface $validator,
        private readonly ValidationHelper   $validationHelper,
        private readonly GpuService         $gpuService,
    ) {
        parent::__construct($bot);
    }

    /**
     * @noinspection PhpUnused
     * @throws TelegramSDKException
     */
    public function saveRig(Update $update): void
    {
        if ($this->hasCallbackData($update)) {
            $this->getDialog()->setRigId($this->valueFilterHelper->getIntFrom($update->callbackQuery->data));
            $this->deleteMessage($update);

            $this->askBrand();
        }
    }

    public function getDialog(): AddGpuDialog
    {
        return $this->dialog;
    }

    /**
     * @throws TelegramSDKException
     */
    private function askBrand(): void
    {
        $reply_markup = ($this->getInlineKeyboard())
            ->row([
                Keyboard::inlineButton(['text' => GpuBrand::Nvidia->name, 'callback_data' => GpuBrand::Nvidia->value]),
                Keyboard::inlineButton(['text' => GpuBrand::Amd->name, 'callback_data' => GpuBrand::Amd->value]),
                Keyboard::inlineButton(['text' => GpuBrand::Intel->name, 'callback_data' => GpuBrand::Intel->value]),
            ]);
        $this->sendMessage('Выберите производителя карты', $reply_markup);
    }

    /**
     * @noinspection PhpUnused
     * @throws TelegramSDKException
     */
    public function askGpu(Update $update): void
    {
        if ($this->hasCallbackData($update)) {
            $this->deleteMessage($update);

            $reply_markup = $this->getInlineKeyboard();
            $listGpu = $this->gpuService->getListGpuByBrand($update->callbackQuery->data);

            $i = 0;
            $columnIndex = 0;
            $row = [];

            while (count($listGpu) > $i) {
                if ($columnIndex < 3) {
                    $row[$columnIndex] = Keyboard::inlineButton([
                        'text'          => trim(mb_strstr($listGpu[$i]->getName(), ' ')),
                        'callback_data' => $listGpu[$i]->getId(),
                    ]);
                    $columnIndex++;
                    $i++;
                } else {
                    $reply_markup->row($row);
                    $row = [];
                    $columnIndex = 0;
                }

                if (
                    count($listGpu) === $i
                    && $row
                ) {
                    $reply_markup->row($row);
                }
            }

            $this->sendMessage('Выберите видеокарту', $reply_markup);
        }
    }

    /**
     * @noinspection PhpUnused
     * @throws TelegramSDKException
     */
    public function saveGpu(Update $update): void
    {
        if ($this->hasCallbackData($update)) {
            $gpuId = $this->valueFilterHelper->getIntFrom($update->callbackQuery->data);
            $this->getDialog()->getGpuDTO()->setId($gpuId);
            $this->deleteMessage($update);

            $this->askCountGpu();
        }
    }

    /**
     * @throws TelegramSDKException
     */
    private function askCountGpu(): void
    {
        $this->sendMessage('Введите количество видеокарт данной модели');
    }

    /**
     * @noinspection PhpUnused
     * @throws TelegramSDKException
     */
    public function checkCountGpu(Update $update): void
    {
        $messageText = $update->message?->text;

        if ($messageText) {
            $gpuCount = $this->valueFilterHelper->getIntFrom($messageText);
            $this->getDialog()->getGpuDTO()->setCount($gpuCount);

            $errors = $this->validator->validate($this->getDialog()->getGpuDTO(), groups: GpuDTO::STEP_COUNT);
            $errorsMessage = $this->validationHelper->getErrorMessageByErrors($errors);

            if ($errorsMessage) {
                $this->sendMessage($errorsMessage);
                $this->transition(__FUNCTION__);
            } else {
                $this->getDialog()->addCurrentGpuDTOInList();

                $this->askAddGpu();
            }
        }
    }

    /**
     * @throws TelegramSDKException
     */
    private function askAddGpu(): void
    {
        $reply_markup = ($this->getInlineKeyboard())
            ->row([
                Keyboard::inlineButton(['text' => 'Да', 'callback_data' => 'yes']),
                Keyboard::inlineButton(['text' => 'Нет', 'callback_data' => 'no']),
            ]);
        $this->sendMessage('Добавить еще карту?', $reply_markup);
    }

    /**
     * @noinspection PhpUnused
     * @throws TelegramSDKException
     * @throws NotFoundException
     */
    public function checkResponseAddGpu(Update $update): void
    {
        if ($this->hasCallbackData($update)) {
            if ($update->callbackQuery->data === 'yes') {
                $this->askBrand();
                $this->transition('askGpu');
            } else {
                $this->rigService->addListGpuInRig($this->getDialog()->getListGpu(), $this->getDialog()->getRigId());
                $this->sendMessage('Карты добавлены');
            }

            $this->deleteMessage($update);
        }
    }
}
