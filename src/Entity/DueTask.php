<?php

namespace App\Entity;

use App\Repository\DueTaskRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DueTaskRepository::class)]
class DueTask
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $dateDueTask = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $startHour = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $endHour = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $comment = null;

    #[ORM\ManyToOne(inversedBy: 'dueTasks')]
    #[ORM\JoinColumn(name: 'collaborator_id', referencedColumnName: 'collaborator_id', nullable: false)]
    #[ORM\JoinColumn(name: 'task_id', referencedColumnName: 'task_id', nullable: false)]
    private ?Assignment $assignment = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateDueTask(): ?\DateTimeImmutable
    {
        return $this->dateDueTask;
    }

    public function setDateDueTask(?\DateTimeImmutable $dateDueTask): static
    {
        $this->dateDueTask = $dateDueTask;

        return $this;
    }

    public function getStartHour(): ?\DateTimeImmutable
    {
        return $this->startHour;
    }

    public function setStartHour(?\DateTimeImmutable $startHour): static
    {
        $this->startHour = $startHour;

        return $this;
    }

    public function getEndHour(): ?\DateTimeImmutable
    {
        return $this->endHour;
    }

    public function setEndHour(?\DateTimeImmutable $endHour): static
    {
        $this->endHour = $endHour;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }

    public function getAssignment(): ?Assignment
    {
        return $this->assignment;
    }

    public function setAssignment(?Assignment $assignment): static
    {
        $this->assignment = $assignment;

        return $this;
    }
}