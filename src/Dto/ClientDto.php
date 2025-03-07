<?php

namespace App\Dto;

class ClientDto
{
    public function __construct(
        private ?string $task = null,
        private ?string $state = null,
        private ?string $date = null,
        private ?string $fullName = null,
        private ?string $duration = null,
        private ?string $tjm = null,
        private ?string $totalHT = null,
        private ?string $tva = null,
        private ?float  $totalTTC = null,
        private ?float  $totalMonth = 0,
    ) {
    }

    public function getTask(): string
    {
        return $this->task;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function getDate(): string
    {
        return $this->date;
    }
    public function getFullName(): string
    {
        return $this->fullName;
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

    public function getTotalTtc(): float
    {
        return $this->totalTTC ?? 0.0;
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

}
