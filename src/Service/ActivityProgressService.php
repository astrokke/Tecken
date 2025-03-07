<?php

namespace App\Service;

use App\Entity\Activity;
use App\Entity\Task;

class ActivityProgressService
{
    public function calculateProgress(Activity $activity): float
    {
        $tasks = $activity->getTasks();
        $totalTasks = count($tasks);
        $completedTasks = $tasks->filter(function($task) {
            return $task->getState()->getLabel() === 'Terminé';
        })->count();

        if ($totalTasks === 0) {
            return 0;
        }

        return ($completedTasks / $totalTasks) * 100;
    }

    public function getProgressClass(float $percentage): string
    {
        if ($percentage >= 70) {
            return 'pgbar-complete';
        } elseif ($percentage >= 50) {
            return 'pgbar-midcomplete';
        } else {
            return 'pgbar-uncomplete';
        }
    }
    public function calculateTaskProgress(Task $task): int
{
    $totalDuration = $task->getDurationForecast() ?? 0;
    $completedDuration = $task->getDurationInvoiceReal() ?? 0;

    if ($totalDuration > 0) {
        return min(100, round(($completedDuration / $totalDuration) * 100));
    }

    return 0;
}
}