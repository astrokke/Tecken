<?php

namespace App\Entity;

use App\Repository\StateRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StateRepository::class)]
class State
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $label = null;

    /**
     * @var Collection<int, Task>
     */
    #[ORM\OneToMany(targetEntity: Task::class, mappedBy: 'state')]
    private Collection $hasTheState;

    public function __construct()
    {
        $this->hasTheState = new ArrayCollection();
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
     * @return Collection<int, Task>
     */
    public function getHasTheState(): Collection
    {
        return $this->hasTheState;
    }

    public function addHasTheState(Task $hasTheState): static
    {
        if (!$this->hasTheState->contains($hasTheState)) {
            $this->hasTheState->add($hasTheState);
            $hasTheState->setState($this);
        }

        return $this;
    }

    public function removeHasTheState(Task $hasTheState): static
    {
        if ($this->hasTheState->removeElement($hasTheState)) {
            // set the owning side to null (unless already changed)
            if ($hasTheState->getState() === $this) {
                $hasTheState->setState(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->label;
    }

}