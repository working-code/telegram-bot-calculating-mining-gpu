<?php

namespace App\Entity;

use App\Repository\WorkRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: WorkRepository::class)]
#[ORM\Index(name: 'work__gpu_id__ind', columns: ['gpu_id'])]
#[ORM\HasLifecycleCallbacks]
class Work
{
    use DateTimeTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'works')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    private Gpu $gpu;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    private string $alias;

    /**
     * @var Collection<int, WorkItem>
     */
    #[ORM\OneToMany(targetEntity: WorkItem::class, mappedBy: 'work', orphanRemoval: true, indexBy: 'alias')]
    private Collection $items;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    /**
     * @return Collection<int, WorkItem>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(WorkItem $item): static
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setWork($this);
        }

        return $this;
    }

    public function removeItem(WorkItem $item): static
    {
        $this->items->removeElement($item);

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getAlias(): string
    {
        return $this->alias;
    }

    public function setAlias(string $alias): self
    {
        $this->alias = $alias;

        return $this;
    }
}
