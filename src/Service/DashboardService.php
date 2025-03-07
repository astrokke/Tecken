<?php

namespace App\Service;

use App\DtoRepository\ClientDtoRepository;
use App\DtoRepository\CsvRepository;
use App\Dto\DashboardDto;
use App\Entity\Activity;
use App\Entity\User;
use App\Repository\ActivityRepository;
use App\Repository\TaskRepository;
use Symfony\UX\Chartjs\Model\Chart;

class DashboardService
{
    public function __construct(
        private TaskService             $taskService,
        private ActivityService         $activityService,
        private ActivityProgressService $progressService,
        private ChartService            $chartService,
        private DateConvertService      $dateService,
        private TaskRepository          $taskRepository,
        private ActivityRepository      $activityRepository,
        private CsvService              $csvService,
        private CsvRepository           $csvRepository,
    ) {
    }
    public function getDataForWeek(
        \DateTimeImmutable $start,
        \DateTimeImmutable $end,
    ) {
        $activitiesStats = $this->taskRepository->findAllActivities($start, $end);
        $totalTasks = array_sum(array_column($activitiesStats, 'task_count'));       // appel du service pour générer le graphique et les sommes
        $chart = $this->chartService->activitiesRender($activitiesStats, 'Tâches effectuées cette semaine');
        return [
            'chart' => $chart['chart'],
            'totalTasks' => $totalTasks,
            'sums' => $chart['sums'],
        ];
    }

    public function getDataForYear(
        \DateTimeImmutable $start,
        \DateTimeImmutable $end,
    ) {
        $activitiesStats = $this->taskRepository->findAllActivities($start, $end);
        $totalTasks = array_sum(array_column($activitiesStats, 'task_count'));
        $chart = $this->chartService->activitiesRender($activitiesStats, 'Tâches effectuées cette année');
        return [
            'totalTasks' => $totalTasks,
            'chart' => $chart['chart'],
            'sums' => $chart['sums'],
        ];
    }

    public function getDataForMonth(
        \DateTimeImmutable $start,
        \DateTimeImmutable $end,
    ) {
        $activitiesStats = $this->taskRepository->findAllActivities($start, $end);
        $totalTasks = array_sum(array_column($activitiesStats, 'task_count'));
        $chart = $this->chartService->activitiesRender($activitiesStats, 'Tâches effectuées ce mois');
        return [
            'chart' => $chart['chart'],
            'totalTasks' => $totalTasks,
            'sums' => $chart['sums'],
        ];
    }

    public function getAllActivitiesDatas(): array
    {
        $activitiesDatas = $this->activityService->getTasksByActivity();

        $chart = $this->chartService->getStatusTasksByActivity($activitiesDatas);
        return [
            'activitiesDatas' => $activitiesDatas,
            'chart' => $chart,
        ];
    }

    private function getActivityStatisticsByPeriod(
        \DateTimeImmutable $start,
        \DateTimeImmutable $end
    ) {
        $tasks = $this->taskRepository->findTasksByPeriodByActivity($start, $end);

        $statistics = [
            'billable' => [],
            'notBillable' => []
        ];

        foreach ($tasks as $task) {
            if ($task['billable']) {
                $statistics['billable'][] = $task;
            } else {
                $statistics['notBillable'][] = $task;
            }
        }

        return $statistics;
    }
    public function getBillableActivityChart(
        \DateTimeImmutable  $start,
        \DateTimeImmutable  $end,
        string              $period
    ): Chart {
        $statistics = $this->getActivityStatisticsByPeriod($start, $end);

        return $this->chartService->getBillableActivities($statistics, $period);
    }

    public function getTaskStateByUser(User $user): array
    {
        $statUser = $this->taskRepository->getTaskStatisticsByUser($user);
        return $this->chartService->getTaskStateByUser($user, $statUser);
    }

    public function getTypeTaskByUser(User $user): Chart
    {
        $typesDatas = $this->taskRepository->getTaskTypeCountByUser($user);
        return $this->chartService->getTaskTypeByUser($user, $typesDatas);
    }

    public function exportCsv(
        ?Activity   $activity,
        string      $month,
        \DateTimeImmutable $date,
    ) {
        $csvDatas = $this->csvRepository->getCsvByMonth($month, $activity);
        return $this->csvService->exportCsvByMonth($activity, $date, $csvDatas);
    }

}
