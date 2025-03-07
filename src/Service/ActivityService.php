<?php

namespace App\Service;

use App\Entity\Activity;
use App\Repository\ActivityRepository;
use App\Repository\UserRepository;
use App\Repository\TypeOfTaskRepository;
use App\Repository\StateRepository;
use Doctrine\Common\Collections\Collection;

class ActivityService
{
    public function __construct(
        private ActivityRepository $activityRepository,
        private UserRepository $userRepository,
        private TypeOfTaskRepository $typeOfTaskRepository,
        private StateRepository $stateRepository,
        private ActivityProgressService $progressService
    ) {
    }

    public function getActivityData(Activity $activity): ?array
    {
        return $this->prepareActivityData($activity);
    }

    public function getAllActivitiesData(): array
    {
        $activities = $this->activityRepository->findAll();
        $activitiesData = [];

        foreach ($activities as $activity) {
            $activitiesData[] = $this->prepareActivityData($activity);
        }

        return [
            'activities' => $activitiesData,
            'allCollaborators' => $this->userRepository->findAll(),
            'taskTypes' => $this->typeOfTaskRepository->findAll(),
            'taskStates' => $this->stateRepository->findAll(),
        ];
    }

    private function prepareActivityData(Activity $activity): array
    {
        $progress = $this->progressService->calculateProgress($activity);
        $plannedTasks = [];
        $taskCollabs = [];

        $tasks = $activity->getTasks()->toArray();
        usort($tasks, function ($a, $b) {
            return $a->getStartDateForecast() <=> $b->getStartDateForecast();
        });

        foreach ($tasks as $task) {
            $assignedCollabs = [];
            $isPlanned = false;
            foreach ($task->getAssignments() as $assignment) {
                $collaborator = $assignment->getCollaborator();
                $assignedCollabs[] = $collaborator->getId();
                if ($assignment->getDueTasks()->count() > 0) {
                    $isPlanned = true;
                }
            }
            $plannedTasks[$task->getId()] = $isPlanned;
            $taskCollabs[$task->getId()] = $assignedCollabs;
        }

        return [
            'activity' => $activity,
            'progress' => $progress,
            'progressClass' => $this->progressService->getProgressClass($progress),
            'plannedTasks' => $plannedTasks,
            'taskCollabs' => $taskCollabs,
            'sortedTasks' => $tasks,
            'taskCount' => $this->getTaskCount($activity->getTasks()),
        ];
    }

    private function getTaskCount(Collection $tasks): array
    {
        $total = 0;
        $done = 0;
        foreach ($tasks as $task) {
            $total++;
            if ($task->getState()->getLabel() === 'Terminé') {
                $done++;
            }
        }
        return ['total' => $total, 'done' => $done];
    }

    public function getBillingRateByActivity(): array
    {
        $activities = $this->activityRepository->findAll();
        $billingData = [];

        foreach ($activities as $activity) {
            $billingRate = $activity->IsBillable() ? 100 : 0;

            $billingData[] = [
                'activity_name' => $activity->getName(),
                'billing_rate' => $billingRate
            ];
        }

        return $billingData;
    }

    public function getTasksByActivity(): array
    {
        $activities = $this->activityRepository->findAll();
        $datas = [];

        foreach ($activities as $activity) {
            $tasks = $activity->getTasks();
            $statusCount = [
                'not_started' => 0,
                'in_progress' => 0,
                'completed' => 0,
                'canceled' => 0,
            ];

            foreach ($tasks as $task) {
                $status = $task->getState()->getLabel();
                if ($status === "Non débuté") {
                    $statusCount['not_started']++;
                } elseif ($status === "En cours") {
                    $statusCount['in_progress']++;
                } elseif ($status === "Terminé") {
                    $statusCount['completed']++;
                } elseif ($status === "Annulé") {
                    $statusCount['canceled']++;
                }
            }

            $datas[] = [
                'activity_name' => $activity->getName(),
                'task_counts' => $statusCount,
            ];
        }
        return $datas;
    }


}
