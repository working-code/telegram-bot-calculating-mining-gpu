<?php

namespace App\Entity;

use App\Repository\CoinRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CoinRepository::class)]
#[ORM\UniqueConstraint(name: 'coin__alias__unq', columns: ['alias'])]
#[ORM\HasLifecycleCallbacks]
class Coin
{
    use DateTimeTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    private string $name;

    #[ORM\Column(length: 50, unique: true)]
    #[Assert\NotBlank]
    private string $alias;

    #[ORM\Column()]
    #[Assert\NotBlank]
    private float $price;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $algorithm = null;

    /**
     * @var Collection<int, WorkItem>
     */
    #[ORM\OneToMany(targetEntity: WorkItem::class, mappedBy: 'coin', orphanRemoval: true)]
    private Collection $workItems;

    public function __construct()
    {
        $this->workItems = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
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

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getAlgorithm(): ?string
    {
        return $this->algorithm;
    }

    public function setAlgorithm(?string $algorithm): self
    {
        $this->algorithm = $algorithm;

        return $this;
    }

    /**
     * @return Collection<int, WorkItem>
     */
    public function getWorkItems(): Collection
    {
        return $this->workItems;
    }

    public function addWorkItem(WorkItem $workItem): static
    {
        if (!$this->workItems->contains($workItem)) {
            $this->workItems->add($workItem);
            $workItem->setCoin($this);
        }

        return $this;
    }

    public function removeWorkItem(WorkItem $workItem): static
    {
        $this->workItems->removeElement($workItem);

        return $this;
    }
}
