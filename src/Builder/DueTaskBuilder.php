<?php

namespace App\Builder;

use App\Entity\Assignment;
use App\Entity\DueTask;

class DueTaskBuilder
{
    private \DateTimeImmutable $dateDueTask;
    private \DateTimeImmutable $startHour;
    private \DateTimeImmutable $endHour;
    private ?Assignment $assignment = null;
    private ?string $comment = null;

    public function setDateDueTask(\DateTimeImmutable $dateDueTask): self
    {
        $this->dateDueTask = $dateDueTask;
        return $this;
    }

    public function setStartHour(\DateTimeImmutable $startHour): self
    {
        $this->startHour = $startHour;
        return $this;
    }

    public function setEndHour(\DateTimeImmutable $endHour): self
    {
        $this->endHour = $endHour;
        return $this;
    }

    public function setAssignment(Assignment $assignment): self
    {
        $this->assignment = $assignment;
        return $this;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;
        return $this;
    }

    public function build(): DueTask
    {
        if (!$this->assignment) {
            throw new \Exception('Assignment is required for creating DueTask');
        }

        $dueTask = new DueTask();
        $dueTask->setDateDueTask($this->dateDueTask);
        $dueTask->setStartHour($this->startHour);
        $dueTask->setEndHour($this->endHour);
        $dueTask->setAssignment($this->assignment);
        $dueTask->setComment($this->comment);
        return $dueTask;
    }

    public function update(DueTask $dueTask): DueTask
    {
        $dueTask->setDateDueTask($this->dateDueTask);
        $dueTask->setStartHour($this->startHour);
        $dueTask->setEndHour($this->endHour);
        if($this->assignment !== null) {
            $dueTask->setAssignment($this->assignment);
        }

        $dueTask->setComment($this->comment);
        return $dueTask;
    }
}
