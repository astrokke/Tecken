<?php

namespace App\Service;

use App\DtoRepository\ClientDtoRepository;
use App\DtoRepository\WorkloadRepository;
use App\Entity\Activity;
use App\Entity\Task;
use App\Entity\User;
use App\Repository\AssignmentRepository;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;


use DateTimeImmutable;

class TaskService
{
    public function __construct(
        private UserRepository $userRepository,
        private TaskRepository $taskRepository,
        private WorkloadRepository $workloadRepository,
        private ClientDtoRepository $clientDtoRepository,
        private AssignmentRepository $assignmentRepository,
    ) {
    }

    public function getUserStatistics(
        User $user
    ): array {
        $countTaskDone = 0;
        $countTaskNotDone = 0;

        $tasks = $this->taskRepository->findByUserAndByOrder($user, ['Non débuté', 'En cours']);

        $allTasks = $user->getTasks();
        $totalTasks = count($allTasks);

        foreach($allTasks as $task) {
            $stateLabel = $task->getState()->getLabel();
            if ($stateLabel === "Terminé") {
                $countTaskDone++;
            } else {
                $countTaskNotDone++;
            }
        }

        $countActivity = $user->getActivities()->count();

        return [
            'tasks' => $tasks,
            'countTaskDone' => $countTaskDone,
            'countTaskNotDone' => $countTaskNotDone,
            'totalTasks' => $totalTasks,
            'percentDone' => ($totalTasks > 0) ? intval(($countTaskDone / $totalTasks) * 100) : 0,
            'countActivity' => $countActivity,
        ];
    }

    //TODO: A Ajouter dans un controller
    public function getTotalDueTaskDuration(User $user, Task $task)
    {
        return $this->assignmentRepository->getTotalDurationByTask($user, $task);
    }

    public function getTasksDeadline(User $user): array
    {
        if (!$user) {
            throw new \Exception('User not found');
        }

        $now = new DateTimeImmutable();
        $oneDayLater = $now->modify('+1 day');
        $notifBeforeDl = [];

        // Récupérer les tâches dont la deadline est dans les prochaines 24 heures
        $notifBeforeDl = $this->taskRepository->findTasksDeadline($user->getId(), $now, $oneDayLater);

        // Récupérer les tâches qui ont dépassé leur deadline
        return $notifBeforeDl;
    }
    public function getTasksPastDeadline(User $user): array
    {
        if (!$user) {
            throw new \Exception('User not found');
        }

        $now = new DateTimeImmutable();
        $oneDayLater = $now->modify('+1 day');
        $notifDl = [];

        // Récupérer les tâches qui ont dépassé leur deadline
        $notifDl = $this->taskRepository->findTasksPastDeadline($user->getId(), $now);
        return  $notifDl;
    }
    public function getCompletedTasksByActivity(bool $currentMonthOnly = false): array
    {
        if ($currentMonthOnly) {
            return $this->taskRepository->findCompletedTasksByActivityForCurrentMonth();
        }
        return $this->taskRepository->findCompletedTasksGroupedByActivity();
    }

    public function getUserAssignmentRates(
        \DateTimeImmutable  $startDate,
        \DateTimeImmutable  $endDate,
        User                $user,
        array               $globalColors,
    ): array {
        $totalWeeklyHours = 35;
        $timeTotalByActivities = $this->workloadRepository->findTimeTotalByUser($startDate, $endDate, $user);
        $assignmentRates = [];
        foreach($timeTotalByActivities as $datas) {
            $activityName = $datas->getActivityName();
            $timeTotal = $datas->getTimeTotalAsFloat($datas->getTimeTotal());
            $percentage = ($totalWeeklyHours > 0) ? ($timeTotal / $totalWeeklyHours) * 100 : 0;
            $color = $globalColors[$activityName] ?? $this->generateDistinctColor($globalColors);

            $assignmentRates[] = [
                'activity' => $activityName,
                'time' => $timeTotal,
                'percentage' => round($percentage, 1),
                'color' => $color,
            ];
        }
        return $assignmentRates;

    }

    public function calculateIndividualAssignmentRates(
        \DateTimeImmutable  $startDate,
        \DateTimeImmutable  $endDate,
        array               $globalColors,
    ): array {
        $totalWeeklyHours = 35; //Total d'heures de travail hebdomadaire
        $timeTotalByActivities = $this->workloadRepository->findTimeTotalByDate($startDate, $endDate);
        $assignmentRates = [];

        foreach($timeTotalByActivities as $datas) {
            $firstName = $datas->getFirstName();
            $lastName = $datas->getLastName();
            $fullName = $firstName . ' ' . $lastName;
            // on vérifie si le tableau contient déjà une entrée pour un utilisateur donné,
            // car il peut exister plusieurs lignes qui lui correspondent,
            // et donc être lié à plusieurs activités
            if (!isset($assignmentRates[$fullName])) {
                $assignmentRates[$fullName] = [
                    'user' => [
                        'firstName' => $firstName,
                        'lastName' => $lastName,
                    ],
                    'activities' => [],
                ];
            }
            $activityName = $datas->getActivityName();
            $timeTotal = $datas->getTimeTotalAsFloat($datas->getTimeTotal());
            $percentage = ($totalWeeklyHours > 0) ? ($timeTotal / $totalWeeklyHours) * 100 : 0;
            $color = $globalColors[$activityName] ?? $this->generateDistinctColor($globalColors);

            $assignmentRates[$fullName]['activities'][] = [
                'activity' => $activityName,
                'time' => $timeTotal,
                'percentage' => round($percentage, 1),
                'color' => $color,
            ];
        }
        return $assignmentRates;
    }

    public function calculateGlobalAssignmentRate(
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate,
    ): array {
        $existingColors = [];
        $timeTotalForAll = $this->workloadRepository->findTimeTotalForAllByDate($startDate, $endDate);
        $totalTimeAllActivities = 0;
        foreach($timeTotalForAll as $activityData) {
            $totalTimeAllActivities += $activityData->getTimeTotalAsFloat($activityData->getTimeTotal());
        }
        $activityPercentages = [];
        foreach($timeTotalForAll as $activityData) {
            $activityName = $activityData->getActivityName();
            $timeTotal = $activityData->getTimeTotalAsFloat($activityData->getTimeTotal());
            $percentage = ($totalTimeAllActivities > 0) ? ($timeTotal / $totalTimeAllActivities) * 100 : 0;
            if(!isset($existingColors[$activityName])) {
                $existingColors[$activityName] = $this->generateDistinctColor($existingColors);
            }
            $activityPercentages[] = [
                'activity' => $activityName,
                'percentage' => round($percentage, 1),
                'color' => $existingColors[$activityName],
            ];
        }
        return [
            'activityPercentages' => $activityPercentages,
            'colors' => $existingColors,
        ];

    }

    private function getDateRangeForPeriod(string $period, int $offset): array
    {
        $start = new \DateTimeImmutable();
        $end = clone $start;

        switch ($period) {
            case 'week':
                $start->modify($offset . ' weeks')->modify('monday this week');
                $end->modify($offset . ' weeks')->modify('sunday this week');
                break;
            case 'month':
                $start->modify($offset . ' months')->modify('first day of this month');
                $end->modify($offset . ' months')->modify('last day of this month');
                break;
            case 'year':
                $start->modify($offset . ' years')->modify('first day of january');
                $end->modify($offset . ' years')->modify('last day of december');
                break;
        }

        return ['start' => $start, 'end' => $end];
    }

    public function getPaginatedTasks(
        Activity $activity,
        int $page = 1,
        int $limit = 5,
    ): array {
        $offset = ($page - 1) * $limit;

        $qb = $this->taskRepository->createQueryBuilder('t')
            ->leftJoin('t.state', 's')
            ->where('t.activity = :activity')
            ->setParameter('activity', $activity)
            ->orderBy(
                "
                CASE 
                    WHEN s.label = 'Non débuté' THEN 1
                    WHEN s.label = 'En cours' THEN 2
                    WHEN s.label  = 'Terminé' THEN 3
                    WHEN s.label = 'Annulé' THEN 4
                    ELSE 5
                END",
                'ASC'
            )
            // ajouter un else avec pstgres pour gérer le cas ou la valeur est inconnues
            ->setFirstResult($offset)
            ->setMaxResults($limit);
        $tasks = $qb->getQuery()->getResult();

        $totalTasks = $this->taskRepository->count(['activity' => $activity]);

        $totalPages = ceil($totalTasks / $limit);

        return [
            'tasks' => $tasks,
            'totalTasks' => $totalTasks,
            'currentPage' => $page,
            'totalPages' => $totalPages
        ];
    }

    public function getTaskStatistics(): array
    {
        $statistics = $this->taskRepository->getTaskStatistics();

        $totalTasks = $statistics['totalTasks'];
        $notStartedTasks = $statistics['notStartedTask'];
        $inProgressTasks = $statistics['inProgressTask'];
        $completedTasks = $statistics['completedTask'];
        $canceledTasks = $statistics['canceledTask'];

        $notStartedPercentage = ($totalTasks > 0) ? ($notStartedTasks / $totalTasks) * 100 : 0;
        $inProgressPercentage = ($totalTasks > 0) ? ($inProgressTasks / $totalTasks) * 100 : 0;
        $completedPercentage = ($totalTasks > 0) ? ($completedTasks / $totalTasks) * 100 : 0;
        $canceledPercentage = ($totalTasks > 0) ? ($canceledTasks / $totalTasks) * 100 : 0;

        return [
            'totalTasks' => $totalTasks,
            'notStartedPercentage' => $notStartedPercentage,
            'inProgressPercentage' => $inProgressPercentage,
            'completedPercentage' => $completedPercentage,
            'canceledPercentage' => $canceledPercentage,
        ];
    }

    private function getColorForActivity(string $activityName, array &$existingColors): string
    {
        // Générer une nouvelle couleur distincte
        $color = $this->generateDistinctColor($existingColors);
        // Add la couleur à la liste des couleurs existantes
        $existingColors[] = $color;
        return $color;
    }

    private function generateDistinctColor(array $existingColors): string
    {
        do {
            $color = sprintf('rgba(%d, %d, %d, 0.6)', rand(0, 255), rand(0, 255), rand(0, 255));
        } while (in_array($color, $existingColors));
        return $color;
    }

    public function getClientData(
        ?Activity   $activity,
        string      $month,
    ) {
        $datas = $this->clientDtoRepository->getDataByActivityForMonth($month, $activity);
        // dd($datas);
        return $datas;
    }

}
