<?php

namespace App\Entity;

use App\Repository\TypeOfTaskRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TypeOfTaskRepository::class)]
class TypeOfTask
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $label = null;

    #[ORM\Column(nullable: true)]
    private ?float $coefHourRate = null;

    /**
     * @var Collection<int, Task>
     */
    #[ORM\OneToMany(targetEntity: Task::class, mappedBy: 'typeOfTask')]
    private Collection $type;

    #[ORM\Column(length: 31, nullable: true)]
    private ?string $color = null;

    public function __construct()
    {
        $this->type = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function getCoefHourRate(): ?float
    {
        return $this->coefHourRate;
    }

    public function setCoefHourRate(?float $coefHourRate): static
    {
        $this->coefHourRate = $coefHourRate;

        return $this;
    }

    /**
     * @return Collection<int, Task>
     */
    public function getType(): Collection
    {
        return $this->type;
    }

    public function addType(Task $type): static
    {
        if (!$this->type->contains($type)) {
            $this->type->add($type);
            $type->setTypeOfTask($this);
        }

        return $this;
    }

    public function removeType(Task $type): static
    {
        if ($this->type->removeElement($type)) {
            // set the owning side to null (unless already changed)
            if ($type->getTypeOfTask() === $this) {
                $type->setTypeOfTask(null);
            }
        }

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): static
    {
        $this->color = $color;

        return $this;
    }
}
