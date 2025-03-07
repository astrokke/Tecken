<?php

namespace App\Entity;

use App\Repository\TaskRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
class Task
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, nullable: false)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $startDateForecast = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $endDateForecast = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Range(min:1, notInRangeMessage: "La durée doit être supérieur à 0 !")]
    private ?int $durationForecast = null;

    #[ORM\Column(nullable: true)]
    private ?float $durationInvoiceReal = null;

    #[ORM\ManyToOne(inversedBy: 'hasTheState')]
    #[ORM\JoinColumn(nullable: false)]
    private ?State $state = null;

    #[ORM\ManyToOne(inversedBy: 'type')]
    #[ORM\JoinColumn(nullable: false)]
    private ?TypeOfTask $typeOfTask = null;

    #[ORM\ManyToOne(inversedBy: 'tasks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Activity $activity = null;

    /**
     * @var Collection<int, Assignment>
     */
    #[ORM\OneToMany(targetEntity: Assignment::class, mappedBy: 'task', orphanRemoval: true)]
    private Collection $assignments;

    #[ORM\ManyToOne(inversedBy: 'tasks')]
    private ?Milestone $milestone = null;


    public function __construct()
    {
        $this->assignments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getStartDateForecast(): ?\DateTimeImmutable
    {
        return $this->startDateForecast;
    }

    public function setStartDateForecast(?\DateTimeImmutable $startDateForecast): static
    {
        $this->startDateForecast = $startDateForecast;

        return $this;
    }

    public function getEndDateForecast(): ?\DateTimeImmutable
    {
        return $this->endDateForecast;
    }

    public function setEndDateForecast(?\DateTimeImmutable $endDateForecast): static
    {
        $this->endDateForecast = $endDateForecast;

        return $this;
    }

    public function getDurationForecast(): ?int
    {
        return $this->durationForecast;
    }

    public function setDurationForecast(?int $durationForecast): static
    {
        $this->durationForecast = $durationForecast;

        return $this;
    }

    public function getDurationInvoiceReal(): ?float
    {
        return $this->durationInvoiceReal;
    }

    public function setDurationInvoiceReal(?float $durationInvoiceReal): static
    {
        $this->durationInvoiceReal = $durationInvoiceReal;

        return $this;
    }

    public function getState(): ?State
    {
        return $this->state;
    }

    public function setState(?State $state): static
    {
        $this->state = $state;

        return $this;
    }

    public function getTypeOfTask(): ?TypeOfTask
    {
        return $this->typeOfTask;
    }

    public function setTypeOfTask(?TypeOfTask $typeOfTask): static
    {
        $this->typeOfTask = $typeOfTask;

        return $this;
    }

    public function getActivity(): ?Activity
    {
        return $this->activity;
    }

    public function setActivity(?Activity $activity): static
    {
        $this->activity = $activity;

        return $this;
    }

    /**
     * @return Collection<int, Assignment>
     */
    public function getAssignments(): Collection
    {
        return $this->assignments;
    }

    public function addAssignment(Assignment $assignment): static
    {
        if (!$this->assignments->contains($assignment)) {
            $this->assignments->add($assignment);
            $assignment->setTask($this);
        }

        return $this;
    }

    public function removeAssignment(Assignment $assignment): static
    {
        if ($this->assignments->removeElement($assignment)) {
            // set the owning side to null (unless already changed)
            if ($assignment->getTask() === $this) {
                $assignment->setTask(null);
            }
        }

        return $this;
    }

    public function getDurationForecastAsString(): string
    {
        if ($this->durationForecast !== null) {
            if ($this->durationForecast < 10) {
                return '0' . $this->durationForecast . 'h';
            }
            return $this->durationForecast . 'h';
        }

        return '01h00';
    }

    public function getTotalDueTaskDurationAsString(): ?string
    {
        $totalMinutes = 0;

        foreach ($this->getAssignments() as $assignment) {
            foreach ($assignment->getDueTasks() as $dueTask) {
                if ($dueTask) {
                    $startHour = $dueTask->getStartHour()->format('H:i');
                    $endHour = $dueTask->getEndHour()->format('H:i');

                    list($startHours, $startMinutes) = explode(':', $startHour);
                    list($endHours, $endMinutes) = explode(':', $endHour);

                    $startTotalMinutes = ($startHours * 60) + $startMinutes;
                    $endTotalMinutes = ($endHours * 60) + $endMinutes;
                    $durationMinutes = $endTotalMinutes - $startTotalMinutes;

                    $totalMinutes += $durationMinutes;
                }
            }
        }

        $totalHours = intdiv($totalMinutes, 60);
        $totalMinutes = $totalMinutes % 60;

        $totalDuration = sprintf('%02dh%02d', $totalHours, $totalMinutes);

        return $totalDuration;
    }

    public function getMilestone(): ?Milestone
    {
        return $this->milestone;
    }

    public function setMilestone(?Milestone $milestone): static
    {
        $this->milestone = $milestone;

        return $this;
    }


    #[Assert\Callback]
    public function validateDates(ExecutionContextInterface $context): void
    {
        if ($this->startDateForecast && $this->endDateForecast) {
            if ($this->endDateForecast < $this->startDateForecast) {
                $context->buildViolation('La date de fin doit être égale ou postérieure à la date de début.')
                    ->atPath('endDateForecast')
                    ->addViolation();
            }
        }
    }
}
