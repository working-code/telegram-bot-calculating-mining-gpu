<?php
declare(strict_types=1);

namespace App\Telegram\Dialog;

use App\Component\TelegramDialog\BaseDialog;
use App\Telegram\Handler\GetSettingsForProfitableAlgorithmsDialogHandler;

class GetSettingsForProfitableAlgorithmsDialog extends BaseDialog
{
    protected array  $steps        = ['askChoiceRig', 'createReport'];
    protected string $handlerClass = GetSettingsForProfitableAlgorithmsDialogHandler::class;
}
