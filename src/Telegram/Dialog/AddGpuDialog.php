<?php
declare(strict_types=1);

namespace App\Telegram\Dialog;

use App\Component\TelegramDialog\BaseDialog;
use App\DTO\GpuDTO;
use App\Telegram\Handler\AddGpuDialogHandler;

class AddGpuDialog extends BaseDialog
{
    protected array  $steps        = [
        'askChoiceRig', 'saveRig', 'askGpu', 'saveGpu', 'checkCountGpu', 'checkResponseAddGpu',
    ];
    protected string $handlerClass = AddGpuDialogHandler::class;

    private int $rigId;

    private GpuDTO $gpuDTO;

    /** @var GpuDTO[] $listGpu * */
    private array $listGpu;

    public function __construct(
        int $chatId
    ) {
        parent::__construct($chatId);
        $this->gpuDTO = new GpuDTO();
    }

    public function getRigId(): int
    {
        return $this->rigId;
    }

    public function setRigId(int $rigId): self
    {
        $this->rigId = $rigId;

        return $this;
    }

    public function addCurrentGpuDTOInList(): void
    {
        $this->listGpu[] = clone $this->gpuDTO;
    }

    public function getListGpu(): array
    {
        return $this->listGpu;
    }

    public function getGpuDTO(): GpuDTO
    {
        return $this->gpuDTO;
    }

    public function setGpuDTO(GpuDTO $gpuDTO): self
    {
        $this->gpuDTO = $gpuDTO;

        return $this;
    }
}
