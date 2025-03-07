<?php

namespace App\Entity;

use App\Repository\TypeOfActivityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TypeOfActivityRepository::class)]
class TypeOfActivity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $label = null;

    /**
     * @var Collection<int, Activity>
     */
    #[ORM\OneToMany(targetEntity: Activity::class, mappedBy: 'typeOfActivity', orphanRemoval: true)]
    private Collection $type;

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

    /**
     * @return Collection<int, Activity>
     */
    public function getType(): Collection
    {
        return $this->type;
    }

    public function addType(Activity $type): static
    {
        if (!$this->type->contains($type)) {
            $this->type->add($type);
            $type->setTypeOfActivity($this);
        }

        return $this;
    }

    public function removeType(Activity $type): static
    {
        if ($this->type->removeElement($type)) {
            // set the owning side to null (unless already changed)
            if ($type->getTypeOfActivity() === $this) {
                $type->setTypeOfActivity(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->label;
    }

}