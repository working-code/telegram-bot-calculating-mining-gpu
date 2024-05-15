<?php
declare(strict_types=1);

namespace App\Telegram\Handler;

use App\Component\TelegramDialog\BaseDialogHandler;
use App\DTO\RigDTO;
use App\Exception\ValidationErrorException;
use App\Helper\ValidationHelper;
use App\Helper\ValueFilterHelper;
use App\Service\RigService;
use App\Telegram\Dialog\AddRigDialog;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Telegram\Bot\Objects\Update;

class AddRigDialogHandler extends BaseDialogHandler
{
    public function __construct(
        Api                                 $bot,
        private readonly ValidatorInterface $validator,
        private readonly ValueFilterHelper  $valueFilterHelper,
        private readonly ValidationHelper   $validationHelper,
        private readonly RigService         $rigService,
    ) {
        parent::__construct($bot);
    }

    /**
     * @noinspection PhpUnused
     * @throws TelegramSDKException
     */
    public function askNameRig(Update $update): void
    {
        $this->sendMessage('Введите название рига');
        $this->settingUserDTO($update);
    }

    private function settingUserDTO(Update $update): void
    {
        ($this->getDialog()->getUserDTO())
            ->setId($update->getChat()->id)
            ->setUserName($update->getChat()->username)
            ->setFirstName($update->getChat()->first_name)
            ->setType($update->getChat()->type);
    }

    public function getDialog(): AddRigDialog
    {
        return $this->dialog;
    }

    /**
     * @noinspection PhpUnused
     * @throws TelegramSDKException
     */
    public function checkName(Update $update): void
    {
        $messageText = $update->message?->text;

        if (!empty($messageText)) {
            $rigDTO = $this->getDialog()->getRigDTO();
            $rigDTO->setName($messageText);
            $errors = $this->validator->validate($rigDTO, groups: RigDTO::STEP_NAME);
            $errorsMessage = $this->validationHelper->getErrorMessageByErrors($errors);

            if ($errorsMessage) {
                $this->sendMessage($errorsMessage);
                $this->transition(__FUNCTION__);
            } else {
                $this->askElectricityCost();
            }
        }
    }

    /**
     * @throws TelegramSDKException
     */
    private function askElectricityCost(): void
    {
        $this->sendMessage('Введите стоимость электроэнергии');
    }

    /**
     * @noinspection PhpUnused
     * @throws TelegramSDKException
     */
    public function checkElectricityCost(Update $update): void
    {
        $messageText = $update->message?->text;

        if (isset($messageText)) {
            $rigDTO = $this->getDialog()->getRigDTO();
            $rigDTO->setElectricityCost($this->valueFilterHelper->getFloatFrom($messageText));
            $errors = $this->validator->validate($rigDTO, groups: RigDTO::STEP_ELECTRICITY_COST);
            $errorsMessage = $this->validationHelper->getErrorMessageByErrors($errors);

            if ($errorsMessage) {
                $this->sendMessage($errorsMessage);
                $this->transition(__FUNCTION__);
            } else {
                $this->askPowerSupplyEfficiency();
            }
        }
    }

    /**
     * @throws TelegramSDKException
     */
    private function askPowerSupplyEfficiency(): void
    {
        $this->sendMessage('Введите КПД БП. Если не знаете введите число 75 (среднестатистический блок имеет КПД 75%)');
    }

    /**
     * @noinspection PhpUnused
     * @throws TelegramSDKException
     */
    public function checkPowerSupplyEfficiency(Update $update): void
    {
        $messageText = $update->message?->text;

        if (isset($messageText)) {
            $rigDTO = $this->getDialog()->getRigDTO();
            $rigDTO->setPowerSupplyEfficiency($this->valueFilterHelper->getIntFrom($messageText));
            $errors = $this->validator->validate($rigDTO, groups: RigDTO::STEP_POWER_SUPPLY_EFFICIENCY);
            $errorsMessage = $this->validationHelper->getErrorMessageByErrors($errors);

            if ($errorsMessage) {
                $this->sendMessage($errorsMessage);
                $this->transition(__FUNCTION__);
            } else {
                $this->askMotherboardConsumption();
            }
        }
    }

    /**
     * @throws TelegramSDKException
     */
    private function askMotherboardConsumption(): void
    {
        $this->sendMessage(
            'Введите Потребление тушки(материнской платы,CPU/RAM/ssd...). '
            . 'Если не знаете, введите число 40 (среднестатистическое потребление 40 ватт)'
        );
    }

    /**
     * @noinspection PhpUnused
     * @throws TelegramSDKException
     */
    public function checkMotherboardConsumption(Update $update): void
    {
        $messageText = $update->message?->text;

        if (isset($messageText)) {
            $rigDTO = $this->getDialog()->getRigDTO();
            $rigDTO->setMotherboardConsumption($this->valueFilterHelper->getIntFrom($messageText));
            $errors = $this->validator->validate($rigDTO, groups: RigDTO::STEP_MOTHERBOARD_CONSUMPTION);
            $errorsMessage = $this->validationHelper->getErrorMessageByErrors($errors);

            if ($errorsMessage) {
                $this->sendMessage($errorsMessage);
                $this->transition(__FUNCTION__);
            } else {
                $this->createRig($update);
            }
        }
    }

    /**
     * @throws TelegramSDKException
     */
    private function createRig(Update $update): void
    {
        try {
            $this->rigService->createRigBy($this->getDialog()->getRigDTO(), $this->getDialog()->getUserDTO());
            $this->sendMessage('Риг создан');
        } catch (ValidationErrorException $e) {
            $this->sendMessage($e->getMessage());
        }
    }
}
