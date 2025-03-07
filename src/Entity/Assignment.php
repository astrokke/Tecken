<?php

namespace App\Entity;

use App\Repository\AssignmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AssignmentRepository::class)]
class Assignment
{
    #[ORM\Id]
    #[ORM\ManyToOne(inversedBy: 'assignments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $collaborator = null;

    #[ORM\Id]
    #[ORM\ManyToOne(inversedBy: 'assignments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Task $task = null;

    #[ORM\Column(nullable: true)]
    private ?float $hourRate = null;

    /**
     * @var Collection<int, DueTask>
     */
    #[ORM\OneToMany(targetEntity: DueTask::class, mappedBy: 'assignment')]
    private Collection $dueTasks;


    public function __construct()
    {
        $this->dueTasks = new ArrayCollection();
    }

    public function getHourRate(): ?float
    {
        return $this->hourRate;
    }

    public function setHourRate(?float $hourRate): static
    {
        $this->hourRate = $hourRate;

        return $this;
    }

    /**
     * @return Collection<int, DueTask>
     */
    public function getDueTasks(): Collection
    {
        return $this->dueTasks;
    }

    public function addDueTask(DueTask $dueTask): static
    {
        if (!$this->dueTasks->contains($dueTask)) {
            $this->dueTasks->add($dueTask);
            $dueTask->setAssignment($this);
        }

        return $this;
    }

    public function removeDueTask(DueTask $dueTask): static
    {
        if ($this->dueTasks->removeElement($dueTask)) {
            // set the owning side to null (unless already changed)
            if ($dueTask->getAssignment() === $this) {
                $dueTask->setAssignment(null);
            }
        }

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

    public function getTask(): ?Task
    {
        return $this->task;
    }

    public function setTask(Task $task): static
    {
        $this->task = $task;

        return $this;
    }
}