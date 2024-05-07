<?php
declare(strict_types=1);

namespace App\Telegram\Dialog;

use App\Component\TelegramDialog\BaseDialog;
use App\DTO\RigDTO;
use App\DTO\UserDTO;

class AddRigDialog extends BaseDialog
{
    protected array  $steps        = [
        'askNameRig', 'checkName', 'checkElectricityCost', 'checkPowerSupplyEfficiency', 'checkMotherboardConsumption',
    ];
    protected string $handlerClass = AddRigDialogHandler::class;
    private RigDTO   $rigDTO;
    private UserDTO  $userDTO;

    public function __construct(int $chatId)
    {
        parent::__construct($chatId);

        $this->rigDTO = new RigDTO();
        $this->userDTO = new UserDTO();
    }

    public function getRigDTO(): RigDTO
    {
        return $this->rigDTO;
    }

    public function getUserDTO(): UserDTO
    {
        return $this->userDTO;
    }
}
