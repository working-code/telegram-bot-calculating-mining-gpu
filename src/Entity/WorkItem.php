<?php

namespace App\Entity;

use App\Repository\WorkItemRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: WorkItemRepository::class)]
#[ORM\Index(name: 'work_item__work_id__ind', columns: ['work_id'])]
#[ORM\Index(name: 'work_item__coin_id__ind', columns: ['coin_id'])]
#[ORM\HasLifecycleCallbacks]
class WorkItem
{
    use DateTimeTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    private string $alias;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    private string $hashRate;

    #[ORM\Column]
    #[Assert\NotBlank]
    private float $count;

    #[ORM\Column]
    #[Assert\NotBlank]
    private int $powerConsumption;

    #[ORM\ManyToOne(inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    private Work $work;

    #[ORM\ManyToOne(inversedBy: 'workItems')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    private Coin $coin;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAlias(): string
    {
        return $this->alias;
    }

    public function setAlias(string $alias): self
    {
        $this->alias = $alias;

        return $this;
    }

    public function getHashRate(): string
    {
        return $this->hashRate;
    }

    public function setHashRate(string $hashRate): self
    {
        $this->hashRate = $hashRate;

        return $this;
    }

    public function getCount(): float
    {
        return $this->count;
    }

    public function setCount(float $count): self
    {
        $this->count = $count;

        return $this;
    }

    public function getPowerConsumption(): int
    {
        return $this->powerConsumption;
    }

    public function setPowerConsumption(int $powerConsumption): self
    {
        $this->powerConsumption = $powerConsumption;

        return $this;
    }

    public function getWork(): Work
    {
        return $this->work;
    }

    public function setWork(Work $work): self
    {
        $this->work = $work;

        return $this;
    }

    public function getCoin(): Coin
    {
        return $this->coin;
    }

    public function setCoin(Coin $coin): self
    {
        $this->coin = $coin;

        return $this;
    }
}
