<?php

namespace App\Entity;

use App\Repository\CostRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CostRepository::class)]
class Cost
{
    #[ORM\Id]
    #[ORM\ManyToOne(inversedBy: 'costs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Activity $activity = null;

    #[ORM\Id]
    #[ORM\ManyToOne(inversedBy: 'costs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $collaborator = null;

    #[ORM\Column(length: 255)]
    private ?string $label = null;

    #[ORM\Column]
    private ?float $hourRate = null;

    public function getActivity(): ?Activity
    {
        return $this->activity;
    }

    public function setActivity(Activity $activity): static
    {
        $this->activity = $activity;

        return $this;
    }
    
    public function getCollaborator(): ?User
    {
        return $this->collaborator;
    }

    public function setCollaborator(User $collaborator): static
    {
        $this->collaborator = $collaborator;

        return $this;
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

    public function getHourRate(): ?float
    {
        return $this->hourRate;
    }

    public function setHourRate(float $hourRate): static
    {
        $this->hourRate = $hourRate;

        return $this;
    }
}
