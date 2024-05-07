<?php

namespace App\Entity;

use App\Entity\Enum\GpuBrand;
use App\Repository\GpuRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: GpuRepository::class)]
#[ORM\UniqueConstraint(name: 'gpu__alias__unq', columns: ['alias'])]
#[ORM\HasLifecycleCallbacks]
class Gpu
{
    use DateTimeTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    private string $name;

    #[ORM\Column(type: 'smallint', nullable: false, enumType: GpuBrand::class)]
    #[Assert\NotBlank]
    private GpuBrand $brand;

    #[ORM\Column(length: 30, unique: true)]
    #[Assert\NotBlank]
    private string $alias;

    /**
     * @var Collection<int, Work>
     */
    #[ORM\OneToMany(targetEntity: Work::class, mappedBy: 'gpu', orphanRemoval: true)]
    private Collection $works;

    public function __construct()
    {
        $this->works = new ArrayCollection();
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

    public function getBrand(): GpuBrand
    {
        return $this->brand;
    }

    public function setBrand(GpuBrand $brand): self
    {
        $this->brand = $brand;

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

    /**
     * @return Collection<int, Work>
     */
    public function getWorks(): Collection
    {
        return $this->works;
    }

    public function addWork(Work $work): static
    {
        if (!$this->works->contains($work)) {
            $this->works->add($work);
            $work->setGpu($this);
        }

        return $this;
    }

    public function removeWork(Work $work): static
    {
        $this->works->removeElement($work);

        return $this;
    }
}
