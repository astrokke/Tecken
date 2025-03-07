<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\ActivityRepository;
use App\Repository\TaskRepository;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class ChartService
{
    public function __construct(
        private ChartBuilderInterface   $chartBuilder,
        private ActivityRepository      $activityRepository,
        private TaskRepository          $taskRepository,
    ) {
    }

    public function activitiesRender(
        array $activities,
        string $label,
    ) {
        $usedColors = [];
        // extract les noms des activités
        $labelsActivity = array_column($activities, 'activity_name');
        $bgColors = [];
        $datas = [];
        $sums = [];
        foreach($activities as $activity) {
            // on récupère les informations
            $activityName = $activity['activity_name'];
            $datas[] = $activity['task_count'] ?? 0;
            $bgColor = $this->generateDistinctColor($usedColors);
            $bgColors[] = $bgColor;
            $sums[] = [
                'name' => $activityName,
                'color' => $bgColor,
                'sums' => $activity['total_billable'],
            ];
        }
        // on construit le graph
        $chart = $this->chartBuilder->createChart(Chart::TYPE_DOUGHNUT);
        $chart->setData([
            'labels' => $labelsActivity,
            'datasets' => [
                [
                    'label' => $label,
                    'backgroundColor' => $bgColors,
                    'data' => $datas,
                ],
            ],
        ]);

        $chart->setOptions([
            'responsive' => true,
            'maintainAspectRatio' => false,
        ]);

        return [
            'chart' => $chart,
            'sums' => $sums,
        ];

    }


    public function getStatusTasksByActivity(array $activitiesData)
    {
        $labelsActivity = [];
        $notStartedData = [];
        $inProgressData = [];
        $completedData = [];
        $canceledData = [];
        $usedColors = [];

        foreach ($activitiesData as $activityStats) {
            $labelsActivity[] = $activityStats['activity_name'];
            $taskCounts = $activityStats['task_counts'];

            $notStartedData[] = $taskCounts['not_started'];
            $inProgressData[] = $taskCounts['in_progress'];
            $completedData[] = $taskCounts['completed'];
            $canceledData[] = $taskCounts['canceled'];

        }

        $chart = $this->chartBuilder->createChart(Chart::TYPE_BAR);
        $chart->setData([
            'labels' => $labelsActivity,
            'datasets' => [
                [
                    'label' => 'Tâches non débutées',
                    'backgroundColor' => 'rgb(87, 117, 144)',
                    'data' => $notStartedData,
                ],
                [
                    'label' => 'Tâches en cours',
                    'backgroundColor' => 'rgb(249, 199, 79)',
                    'data' => $inProgressData,
                ],
                [
                    'label' => 'Tâches terminées',
                    'backgroundColor' => 'rgb(144, 190, 109)',
                    'data' => $completedData,
                ],
                [
                    'label' => 'Tâches annulées',
                    'backgroundColor' => 'rgb(249, 65, 68)',
                    'data' => $canceledData,
                ],
            ],
        ]);

        $chart->setOptions([
            'responsive' => true,
            'maintainAspectRatio' => false,
            'scales' => [
                'x' => [
                    'stacked' => true,
                ],
                'y' => [
                    'stacked' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Totaux des tâches',
                    ],

                ],
            ],
        ]);

        return $chart;
    }

    private function generateDistinctColor(array $existingColors): string
    {
        do {
            $color = sprintf('rgba(%d, %d, %d, 0.6)', rand(0, 255), rand(0, 255), rand(0, 255));
        } while (in_array($color, $existingColors));
        return $color;
    }

    public function getBillableActivities(
        array $statistics,
        string $period,
    ): Chart {
        $usedColors = [];
        $activityNames = [];
        $billableDurations = [];
        $notBillableDurations = [];

        foreach($statistics['billable'] as $stat) {
            $activityNames[] = $stat['activityName'];
            $billableDurations[] = $stat['totalDuration'];
            $notBillableDurations[] = 0;
        }

        foreach ($statistics['notBillable'] as $stat) {
            $index = array_search($stat['activityName'], $activityNames);
            if ($index !== false) {
                $notBillableDurations[$index] = $stat['totalDuration'];
            } else {
                $activityNames[] = $stat['activityName'];
                $billableDurations[] = 0;
                $notBillableDurations[] = $stat['totalDuration'];
            }
        }
        $chart = $this->chartBuilder->createChart(Chart::TYPE_BAR);
        $chart->setData([
            'labels' => $activityNames,
            'datasets' => [
                [
                    'label' => 'Activités Facturable',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'borderWidth' => 1,
                    'data' => $billableDurations,
                ],
                [
                    'label' => 'Activités Non-Facturable',
                    'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                    'borderColor' => 'rgba(255, 99, 132, 1)',
                    'borderWidth' => 1,
                    'data' => $notBillableDurations,
                ],
            ],
        ]);

        $chart->setOptions([
            'responsive' => true,
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Nombre d\'heures facturables',
                    ],
                ],
            ],
            'plugins' => [
                'title' => [
                    'display' => true,
                    'text' => 'Statistiques par ' . ucfirst($period),
                ],
            ],
        ]);
        return $chart;
    }

    public function getTaskStateByUser(User $user, array $stats): array
    {
        $totalTasks = $stats['totalTasks'];
        $chartData = [];

        $states = [
            'Non débuté' => ['color' => 'rgb(87, 117, 144)', 'key' => 'notStartedTask'],
            'En cours' => ['color' => 'rgb(249, 199, 79)', 'key' => 'inProgressTask'],
            'Terminé' => ['color' => 'rgb(144, 190, 109)', 'key' => 'completedTask'],
            'Annulé' => ['color' => 'rgb(249, 65, 68)', 'key' => 'canceledTask'],
        ];

        foreach ($states as $stateLabel => $stateInfo) {
            $stateKey = $stateInfo['key'];
            $stateCount = $stats[$stateKey] ?? 0;
            $percentage = ($totalTasks > 0) ? ($stateCount / $totalTasks) * 100 : 0;
            $borderColor = $stateInfo['color'];
            $chart = $this->chartBuilder->createChart(Chart::TYPE_DOUGHNUT);
            $chart->setData([
                'labels' => [$stateLabel],
                'datasets' => [
                    [
                        'label' => $stateLabel,
                        'backgroundColor' => [
                            $borderColor,
                            'rgba(227, 227, 227)', // Transparent color for remaining
                        ],
                        'borderColor' => $borderColor, // Outer circle border color
                        'borderWidth' => 2, // Width of the outer circle
                        'data' => [round($percentage, 1), round(100 - $percentage, 1)],
                    ],
                ],
            ]);
            $chart->setOptions([
                'responsive' => true,
                'maintainAspectRatio' => false,
                'plugins' => [
                    'legend' => ['display' => false],
                ],
                'cutoutPercentage' => 50, // Adjust this value to set the inner radius
                'elements' => [
                'arc' => [
                    'borderWidth' => 2,
                ],
            ],
            ]);

            // Return an array with chart, percentage, and stateCount
            $chartData[$stateLabel] = [
                'chart' => $chart,
                'percentage' => round($percentage, 1),
                'stateCount' => $stateCount,
                'borderColor' => $borderColor,
            ];
        }

        return $chartData;
    }

    public function getTaskTypeByUser(User $user, array $typesDatas): Chart
    {
        $labels = [];
        $datasets = [];

        foreach ($typesDatas as $taskType) {
            $labels[] = $taskType['task_type'];
            $datasets[] = [
                'label' => $taskType['task_type'],
                'backgroundColor' => 'white',
                'borderColor' => 'white',
                'borderWidth' => 1,
                'data' => [$taskType['task_count']],
            ];
        }

        $chart = $this->chartBuilder->createChart(Chart::TYPE_BAR);
        $chart->setData([
            'labels' => [''],
            'datasets' => $datasets,
        ]);

        $chart->setOptions([
            'scales' => [
                'x' => [
                    'ticks' => [
                        'color' => 'rgba(255, 255, 255, 1)',
                    ],
                    'grid' => [
                        'color' => 'rgba(255, 255, 255, 0.2)',
                    ],
                ],
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'color' => 'rgba(255, 255, 255, 1)',
                    ],
                    'grid' => [
                        'color' => 'rgba(255, 255, 255, 0.2)',
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'labels' => [
                        'color' => 'rgba(255, 255, 255, 1)',
                    ],
                ],
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
        ]);

        return $chart;
    }


}
