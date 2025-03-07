<?php
namespace App\Dto;

use DateTimeImmutable;

class MilestoneDto 
{
    /**
     * @param array<int, MilestoneUserDto> $users
     */
    public function __construct(
        private int                     $activityId,
        private int                     $milestoneId,
        private string                  $label,
        private \DateTimeImmutable      $startDate,
        private \DateTimeImmutable      $endDate,
        private int                     $completedTasks,
        private int                     $totalTasks,
        private ?DateTimeImmutable      $planningStart = null,
        private ?DateTimeImmutable      $planningStop = null,
        private ?string                 $activityLabel = null,
        private ?int                    $planningTotalDays = null,
        private ?array                  $users = null, 
        private ?float                  $percentCompletion = 0,
        private float                   $startPercent = 0.0,
        private ?int                    $totalDay = null,
    )
    {
        if($planningStart->format("Y-m-d") !== $planningStop->format("Y-m-d")){
            $offset = 0;
            if($startDate>$planningStart && $endDate < $planningStop){
                $dateDif = date_diff($startDate, $endDate);
                $offset = intval(date_diff($planningStart, $startDate)->format('%a'));
            }elseif($startDate<$planningStart && $endDate > $planningStop){
                $dateDif = date_diff($planningStart, $planningStop);
            }elseif($startDate < $planningStart){
                $dateDif = date_diff($planningStart, $endDate);
            }else{
                $dateDif = date_diff($startDate, $planningStop);
                $offset = intval(date_diff($planningStart, $startDate)->format('%a'));
            }
            $this->totalDay = intval($dateDif->format('%a'));
            $this->planningTotalDays = intval(date_diff($planningStart, $planningStop)->format('%a'));
            $this->startPercent = $offset*100/$this->planningTotalDays;
        }
        if($totalTasks !== 0){
            $this->percentCompletion = $completedTasks/$totalTasks*100;
        }
    }

    public function getActivityId(): int 
    {
        return $this->activityId;
    }

    public function getMilestoneId(): int 
    {
        return $this->milestoneId;
    }

    public function getLabel(): string 
    {
        return $this->label;
    }

    public function getActivityLabel(): string|null 
    {
        return $this->activityLabel;
    }

    public function getStartDate(): \DateTimeImmutable 
    {
        return $this->startDate;
    }

    public function getEndDate(): \DateTimeImmutable
    {
        return $this->endDate;
    }

    public function getCompletedTasks(): int 
    {
        return $this->completedTasks;
    }

    public function getTotalTasks(): int 
    {
        return $this->totalTasks;
    }

    public function getPercentCompletion(): float 
    {
        return $this->percentCompletion;
    }

    public function getStartPercent(): float 
    {
        return $this->startPercent;
    }

    public function getTotalDay(): float 
    {
        return $this->totalDay;
    }

    public function getPlanningStart(): \DateTimeImmutable
    {
        return $this->planningStart;
    }

    public function getPlanningStop(): \DateTimeImmutable
    {
        return $this->planningStop;
    }

    public function getPlanningTotalDays(): int
    {
        return $this->planningTotalDays;
    }

    /**
     * @return array<int,MilestoneUserDto>
     */
    public function getUsers(): array 
    {
        return $this->users;
    }

    /**
     * @param array<int,MilestoneUserDto> $users
     */
    public function setUsers(array $users): MilestoneDto
    {
        $this->users = $users;
        return $this;
    }
}
