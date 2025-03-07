<?php

namespace App\Dto;

class CsvDto
{
    public function __construct(
        private ?string $activityName = null,
        private ?string $date = null,
        private ?string $fullName = null,
        private ?string $task = null,
        private ?string $duration = null,
        private ?string $tjm = null,
        private ?string $totalHT = null,
        private ?string $tva = null,
        private ?string $totalTTC = null,
        private ?float $totalMonth = 0,
    ) {
    }

    public function getDate(): string
    {
        return $this->date;
    }
    public function getFullName(): string
    {
        return $this->fullName;
    }
    public function getTask(): string
    {
        return $this->task;
    }

    public function getDuration(): string
    {
        return $this->duration;
    }

    public function getTjm(): string
    {
        return $this->tjm;
    }

    public function getTotalHt(): string
    {
        return $this->totalHT;
    }

    public function getTva(): string
    {
        return $this->tva;
    }

    public function getTotalTtc(): string
    {
        return $this->totalTTC;
    }


    public function getTotalMonth(): float
    {
        return $this->totalMonth;
    }

    public function setTotalMonth(float $totalMonth)
    {
        $this->totalMonth = $totalMonth;
        return $this;
    }

    public function getActivityName(): ?string
    {
        return $this->activityName;
    }

    public function setActivityName(?string $activityName)
    {
        $this->activityName = $activityName;
        return $this;
    }
}
