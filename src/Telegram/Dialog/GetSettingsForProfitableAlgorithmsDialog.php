<?php
declare(strict_types=1);

namespace App\Telegram\Dialog;

use App\Component\TelegramDialog\BaseDialog;

class GetSettingsForProfitableAlgorithmsDialog extends BaseDialog
{
    protected array  $steps        = ['askChoiceRig', 'createReport'];
    protected string $handlerClass = GetSettingsForProfitableAlgorithmsHandler::class;
}
