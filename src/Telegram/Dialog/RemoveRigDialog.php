<?php
declare(strict_types=1);

namespace App\Telegram\Dialog;

use App\Component\TelegramDialog\BaseDialog;

class RemoveRigDialog extends BaseDialog
{
    protected array  $steps        = ['askChoiceRig', 'removeRig'];
    protected string $handlerClass = RemoveRigDialogHandler::class;
}
