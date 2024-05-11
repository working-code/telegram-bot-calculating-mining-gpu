<?php

namespace App\Entity;

use App\Repository\RigItemRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RigItemRepository::class)]
#[ORM\Index(name: 'rig_item__rig_id__ind', columns: ['rig_id'])]
#[ORM\Index(name: 'rig_item__gpu_id__ind', columns: ['gpu_id'])]
#[ORM\HasLifecycleCallbacks]
class RigItem
{
    use DateTimeTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    private int $count;

    #[ORM\ManyToOne(inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    private Rig $rig;

    #[ORM\ManyToOne(targetEntity: Gpu::class)]
    #[ORM\JoinColumn(name: 'gpu_id', referencedColumnName: 'id', nullable: false)]
    #[Assert\NotBlank]
    private Gpu $gpu;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function setCount(int $count): self
    {
        $this->count = $count;

        return $this;
    }

    public function getRig(): Rig
    {
        return $this->rig;
    }

    public function setRig(Rig $rig): self
    {
        $this->rig = $rig;

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
