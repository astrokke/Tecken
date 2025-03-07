<?php

namespace App\Dto;

class PlannedTaskDto extends Dto
{
    public function __construct(
        private int     $userId,
        private string  $taskName,
        private ?string $clientName,
        private string  $activityName,
        private string  $taskType,
        private string  $day,
        private string  $startHour,
        private string  $endHour,
        private float   $startPercent,
        private float   $endPercent,
        private string  $color,
    )
    {   
    }
}
