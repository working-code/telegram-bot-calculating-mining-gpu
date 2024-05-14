<?php

namespace App\Entity;

use App\Repository\RigRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RigRepository::class)]
#[ORM\Index(name: 'rig__user_id__ind', columns: ['user_id'])]
#[ORM\HasLifecycleCallbacks]
class Rig
{
    use DateTimeTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    private string $name;

    #[ORM\Column]
    #[Assert\NotBlank]
    private float $electricityCost;

    #[ORM\Column]
    #[Assert\NotBlank]
    private int $powerSupplyEfficiency;

    #[ORM\Column]
    #[Assert\NotBlank]
    private int $motherboardConsumption;

    #[ORM\ManyToOne(inversedBy: 'rig')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    private User $user;

    /**
     * @var Collection<int, RigItem>
     */
    #[ORM\OneToMany(targetEntity: RigItem::class, mappedBy: 'rig', orphanRemoval: true)]
    private Collection $items;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
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

    public function getElectricityCost(): float
    {
        return $this->electricityCost;
    }

    public function setElectricityCost(float $electricityCost): self
    {
        $this->electricityCost = $electricityCost;

        return $this;
    }

    public function getPowerSupplyEfficiency(): int
    {
        return $this->powerSupplyEfficiency;
    }

    public function setPowerSupplyEfficiency(int $powerSupplyEfficiency): self
    {
        $this->powerSupplyEfficiency = $powerSupplyEfficiency;

        return $this;
    }

    public function getMotherboardConsumption(): int
    {
        return $this->motherboardConsumption;
    }

    public function setMotherboardConsumption(int $motherboardConsumption): self
    {
        $this->motherboardConsumption = $motherboardConsumption;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, RigItem>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(RigItem $item): static
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setRig($this);
        }

        return $this;
    }

    public function removeItem(RigItem $item): static
    {
        $this->items->removeElement($item);

        return $this;
    }

    public function hasItems(): bool
    {
        return $this->items->count() > 0;
    }
}
