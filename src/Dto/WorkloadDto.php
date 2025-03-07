<?php

namespace App\Dto;

use DateTimeImmutable;

class WorkloadDto
{
    public function __construct(
        private ?string  $activityName,
        private ?string  $timeTotal = null,
        private ?string  $firstName = null,
        private ?string  $lastName = null,
    ) {
    }

    public function getActivityName(): string
    {
        return $this->activityName;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getTimeTotal(): ?string
    {
        return $this->timeTotal;
    }

    public function getTimeTotalAsFloat(?string $timeTotal): float
    {
        if (!$timeTotal) {
            return 0.0;
        }
        $time = explode(":", $timeTotal);
        $hours = $time[0];
        $minutes = $time[1];
        if ($minutes === "00") {
            return floatval($hours);
        }
        $minutes = "5";
        $duration = $hours  . "." . $minutes;
        return floatval($duration);
    }

    public function setActivityName(string $activityName): static
    {
        $this->activityName = $activityName;
        return $this;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;
        return $this;
    }


    public function setTimeTotal(string $timeTotal): static
    {
        $this->timeTotal = $timeTotal;
        return $this;
    }
}
