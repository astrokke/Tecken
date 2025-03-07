<?php

namespace App\Entity;

use App\Repository\ActivityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ActivityRepository::class)]
class Activity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?bool $billable = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $startDate = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    /**
     * @var Collection<int, Task>
     */
    #[ORM\OneToMany(targetEntity: Task::class, mappedBy: 'activity', orphanRemoval: true)]
    private Collection $tasks;

    #[ORM\ManyToOne(inversedBy: 'type')]
    #[ORM\JoinColumn(nullable: false)]
    private ?TypeOfActivity $typeOfActivity = null;

    #[ORM\ManyToOne(inversedBy: 'activities')]
    private ?Client $client = null;

    /**
     * @var Collection<int, Interlocutor>
     */
    #[ORM\ManyToMany(targetEntity: Interlocutor::class, mappedBy: 'activities')]
    private Collection $interlocutors;

    /**
     * @var Collection<int, Cost>
     */

    #[ORM\OneToMany(targetEntity: Cost::class, mappedBy: 'activity', orphanRemoval: true)]
    private Collection $costs;

    /**
     * @var Collection<int, Milestone>
     */
    #[ORM\OneToMany(targetEntity: Milestone::class, mappedBy: 'activity', orphanRemoval: true)]
    private Collection $milestones;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $endDate = null;


    public function __construct()
    {
        $this->tasks = new ArrayCollection();
        $this->interlocutors = new ArrayCollection();
        $this->costs = new ArrayCollection();
        $this->milestones = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function IsBillable(): ?bool
    {
        return $this->billable;
    }

    public function setBillable(?bool $billable): static
    {
        $this->billable = $billable;

        return $this;
    }

    public function getStartDate(): ?\DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeImmutable $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, Task>
     */
    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function addTask(Task $task): static
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks->add($task);
            $task->setActivity($this);
        }

        return $this;
    }

    public function removeInclude(Task $task): static
    {
        if ($this->tasks->removeElement($task)) {
            // set the owning side to null (unless already changed)
            if ($task->getActivity() === $this) {
                $task->setActivity(null);
            }
        }

        return $this; 
    }

    public function getTypeOfActivity(): ?TypeOfActivity
    {
        return $this->typeOfActivity;
    }

    public function setTypeOfActivity(?TypeOfActivity $typeOfActivity): static
    {
        $this->typeOfActivity = $typeOfActivity;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): static
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return Collection<int, Interlocutor>
     */
    public function getInterlocutors(): Collection
    {
        return $this->interlocutors;
    }

    public function addInterlocutor(Interlocutor $interlocutor): static
    {
        if (!$this->interlocutors->contains($interlocutor)) {
            $this->interlocutors->add($interlocutor);
            $interlocutor->addActivity($this);
        }

        return $this;
    }

    public function removeInterlocutor(Interlocutor $interlocutor): static
    {
        if ($this->interlocutors->removeElement($interlocutor)) {
            $interlocutor->removeActivity($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Cost>
     */
    public function getCosts(): Collection
    {
        return $this->costs;
    }

    public function addCost(Cost $cost): static
    {
        if (!$this->costs->contains($cost)) {
            $this->costs->add($cost);
            $cost->setActivity($this);
        }

        return $this;
    }

    public function removeCost(Cost $cost): static
    {
        if ($this->costs->removeElement($cost)) {
            // set the owning side to null (unless already changed)
            if ($cost->getActivity() === $this) {
                $cost->setActivity(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->getName();
    }

    /**
     * @return Collection<int, Milestone>
     */
    public function getMilestones(): Collection
    {
        return $this->milestones;
    }

    public function addMilestone(Milestone $milestone): static
    {
        if (!$this->milestones->contains($milestone)) {
            $this->milestones->add($milestone);
            $milestone->setActivity($this);
        }

        return $this;
    }

    public function removeMilestone(Milestone $milestone): static
    {
        if ($this->milestones->removeElement($milestone)) {
            // set the owning side to null (unless already changed)
            if ($milestone->getActivity() === $this) {
                $milestone->setActivity(null);
            }
        }

        return $this;
    }

    public function getEndDate(): ?\DateTimeImmutable
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeImmutable $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }
}
