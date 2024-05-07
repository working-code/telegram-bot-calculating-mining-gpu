<?php
declare(strict_types=1);

namespace App\DTO;

use App\Entity\Gpu;
use App\Entity\WorkItem;

class WorkDTO
{
    private string $alias;
    private Gpu $gpu;

    /** @var WorkItem[] */
    private array $items = [];

    public function getAlias(): string
    {
        return $this->alias;
    }

    public function setAlias(string $alias): self
    {
        $this->alias = $alias;

        return $this;
    }

    /**
     * @return WorkItem[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function addItem(WorkItemDTO $workItemDTO): self
    {
        $this->items[$workItemDTO->getAlias()] = $workItemDTO;

        return $this;
    }

    public function getGpu(): Gpu
    {
        return $this->gpu;
    }

    public function setGpu(Gpu $gpu): self
    {
        $this->gpu = $gpu;

        return $this;
    }
}
