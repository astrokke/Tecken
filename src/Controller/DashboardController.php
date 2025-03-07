<?php

namespace App\Controller;

use App\Entity\Activity;
use App\Entity\Task;
use App\Entity\User;
use App\Repository\ActivityRepository;
use App\Repository\StateRepository;
use App\Service\TaskService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Builder\DueTaskBuilder;
use App\DtoRepository\EventRepository;
use App\Entity\DueTask;
use App\Repository\AssignmentRepository;
use App\Repository\DueTaskRepository;
use App\Repository\TaskRepository;
use App\Service\DashboardService;
use App\Service\DateConvertService;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\JsonResponse;

use function PHPSTORM_META\type;

#[Route('/home')]
class DashboardController extends AbstractController
{
    public function __construct(
        private Security                $security,
        private EntityManagerInterface  $em,
        private DateConvertService      $dateService,
        private TaskService             $taskService,
        private DashboardService        $dashboardService,
        private StateRepository         $stateRepository,
        private DueTaskRepository       $dueTaskRepository,
        private EventRepository         $eventRepository,
        private ActivityRepository      $activityRepository,
    ) {
    }

    #[Route('/', name: 'dashboard')]
    public function homeCalendar(
        Request $request,
    ): Response {
        $user = $this->security->getUser();
        $periodView = $request->query->get('periodView', 'week');
        $yearOffset = $request->query->getInt('yearOffset', 0);
        $monthOffset = $request->query->getInt('monthOffset', 0);
        $weekOffset = $request->query->getInt('weekOffset', 0);
        $pieChart = [];
        $chartBillable = null;

        switch ($periodView) {
            case 'week':
                $dates = $this->dateService->getWeekStartAndEnd($weekOffset);
                $weekStart = $dates['start'];
                $weekEnd = $dates['end'];
                $pieChart = $this->dashboardService->getDataForWeek($weekStart, $weekEnd);
                $chartBillable = $this->dashboardService->getBillableActivityChart($weekStart, $weekEnd, 'Semaine');
                $globaleAffectation = $this->taskService->calculateGlobalAssignmentRate($weekStart, $weekEnd);
                $globalColors = $globaleAffectation['colors'];
                $individualAffectation = $this->taskService->calculateIndividualAssignmentRates($weekStart, $weekEnd, $globalColors);
                $myAffectation = $this->taskService->getUserAssignmentRates($weekStart, $weekEnd, $user, $globalColors);
                $periodLabel = sprintf(
                    "du %s au %s",
                    $this->dateService->getWeekInFrench($weekStart),
                    $this->dateService->getWeekInFrench($weekEnd)
                );
                break;

            case 'year':
                $dates = $this->dateService->getYearStartAndEnd($yearOffset);
                $yearStart = $dates['start'];
                $yearEnd = $dates['end'];
                $pieChart = $this->dashboardService->getDataForYear($yearStart, $yearEnd);
                $chartBillable = $this->dashboardService->getBillableActivityChart($yearStart, $yearEnd, 'Année');
                $globaleAffectation = $this->taskService->calculateGlobalAssignmentRate($yearStart, $yearEnd);
                $globalColors = $globaleAffectation['colors'];
                $individualAffectation = $this->taskService->calculateIndividualAssignmentRates($yearStart, $yearEnd, $globalColors);
                $myAffectation = $this->taskService->getUserAssignmentRates($yearStart, $yearEnd, $user, $globalColors);
                $periodLabel = sprintf("en %s", $yearStart->format('Y'));
                break;

            default:
                $dates = $this->dateService->getMonthStartAndEnd((new \DateTimeImmutable())->modify("{$monthOffset} month"));
                $monthStart = $dates['start'];
                $monthEnd = $dates['end'];
                $pieChart = $this->dashboardService->getDataForMonth($monthStart, $monthEnd);
                $chartBillable = $this->dashboardService->getBillableActivityChart($monthStart, $monthEnd, 'Mois');
                $globaleAffectation = $this->taskService->calculateGlobalAssignmentRate($monthStart, $monthEnd);
                $globalColors = $globaleAffectation['colors'];
                $individualAffectation = $this->taskService->calculateIndividualAssignmentRates($monthStart, $monthEnd, $globalColors);
                $myAffectation = $this->taskService->getUserAssignmentRates($monthStart, $monthEnd, $user, $globalColors);
                $periodLabel = "en ". ucfirst(sprintf("%s", $this->dateService->getMonthInFrench($monthStart)));
                break;
        }


        $taskStats = $this->taskService->getUserStatistics($user);
        $taskStatesChart = $this->dashboardService->getTaskStateByUser($user);
        $taskTypesChart = $this->dashboardService->getTypeTaskByUser($user);
        $datasAllActivities = $this->dashboardService->getAllActivitiesDatas();
        $activitiesBillable = $this->activityRepository->findBillableActivities();
        $statesArray = array_map(fn ($state) => [
            'id' => $state->getId(),
            'label' => $state->getLabel(),
        ], $this->stateRepository->findAll());

        return $this->render('pages/dashboard.html.twig', [
            'title' => 'Dashboard',
            'user' => $user,
            'states' => $this->stateRepository->findAll(),
            'statesArray' => $statesArray,
            'countActivity' => $taskStats['countActivity'],
            'percentDone' => $taskStats['percentDone'],
            'tasks' => $taskStats['tasks'],
            'chartActivities' => $pieChart['chart'],
            'sums' => $pieChart['sums'],
            'totalTasks' => $pieChart['totalTasks'],
            'periodView' => $periodView,
            'datasAllActivities' => $datasAllActivities['chart'],
            'monthOffset' => $monthOffset ?? 0,
            'weekOffset' => $weekOffset ?? 0,
            'yearOffset' => $yearOffset ?? 0,
            'periodLabel' => $periodLabel,
            'billableChart' => $chartBillable,
            'individualAffectation' => $individualAffectation,
            'myAffectation' => $myAffectation,
            'globaleAffectation' => $globaleAffectation['activityPercentages'],
            'activitiesBillable' => $activitiesBillable,
            'taskStatesChart' => $taskStatesChart,
            'sumMyTasks' => $taskStats['totalTasks'],
            'taskTypesChart' => $taskTypesChart,
        ]);
    }

    #[Route(
        path: '/export-csv/{id}/{monthOffset}',
        name: 'export_csv',
    )]
    public function exportCsv(
        Request $request,
        int $id,
        int $monthOffset,
    ): Response {

        $currentDate = new \DateTimeImmutable();
        $date = $currentDate->modify("{$monthOffset} month");
        $activity = $this->activityRepository->find($id);
        if (!$activity) {
            $this->addFlash('warning', 'Activité non existante !');
            return $this->redirectToRoute('dashboard');
        }
        return $this->dashboardService->exportCsv($activity, $date->format('n'), $date);
    }

    #[Route(
        path: '/updateStateFromDashboard/{id}',
        name: 'updateStateFromDashboard',
        methods: ['POST']
    )]
    public function updateStateFromDashboard(
        Request                 $request,
        EntityManagerInterface  $em,
        Task                    $task,
    ): Response {

        $data = json_decode($request->getContent(), true);
        $stateId = $data['stateId'] ?? null;
        if (!$stateId) {
            return new JsonResponse(['error' => 'Invalid state ID'], Response::HTTP_BAD_REQUEST);
        }
        $state = $this->stateRepository->find($stateId);
        if (!$state) {
            return new JsonResponse(['error' => 'State not found'], Response::HTTP_NOT_FOUND);
        }

        $task->setState($state);
        $em->persist($task);
        $em->flush();

        return new JsonResponse(['success' => true], Response::HTTP_OK);
        // $route = $request->headers->get('referer');

        // return $this->redirect($route);
    }

    #[Route(
        path: '/getNotifications',
        name: 'getNotifications',
    )]
    public function getNotifications(): Response
    {
        $user = $this->security->getUser();

        $notificationsBeforeDl = $this->taskService->getTasksDeadline($user);

        $notificationsDl = $this->taskService->getTasksPastDeadline($user);
        $concat = array_merge($notificationsDl, $notificationsBeforeDl);
        $countNotif = count($concat);

        return $this->render('partial/_notification.html.twig', [
            'notificationsBeforeDl' => $notificationsBeforeDl,
            'notificationsDl' => $notificationsDl,
            'countNotif' => $countNotif,
        ]);
    }

    #[Route(
        path: '/ajax/calendar/getDueTasks',
        name: 'getDueTasks',
        methods: ['GET'],
    )]
    public function getDueTasks(Request $request): JsonResponse
    {
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(['error' => 'Invalid request type'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $user = $this->security->getUser();
        $startDate = new DateTimeImmutable($request->query->get('start_date'));
        $endDate = new DateTimeImmutable($request->query->get('end_date'));

        $dueTasks = $this->eventRepository->findDueTasksByUser($user->getId(), $startDate, $endDate);
        $json = [];
        foreach ($dueTasks as $dto) {
            $json[] = $dto->toArray();
        }
        return new JsonResponse($json, JsonResponse::HTTP_OK);
    }

    #[Route(
        path: '/ajax/calendar/createDueTask',
        name: 'createDueTask',
        methods: ['POST'],
    )]
    public function createDueTask(
        Request                 $request,
        TaskRepository          $taskRepository,
        AssignmentRepository    $assignmentRepository,
    ): Response {

        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(['error' => 'Invalid request type'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $data = json_decode($request->getContent());

        list($startDate, $startHourStr) = $this->dateService->extractDate($data->start);
        $startHour = new \DateTimeImmutable($startHourStr);
        $duration = new \DateTimeImmutable($data->duration);

        $user = $this->security->getUser();

        $assignment = $assignmentRepository->findOneBy([
            'collaborator' => $user,
            'task' => $taskRepository->find($data->id)
        ]);
        if (!$assignment) {
            throw $this->createNotFoundException('Assignment not found');
        }
        $endHour = $this->dateService->calculEndHour($startHour, $duration);

        $dueTaskBuilder = new DueTaskBuilder();
        $dueTask = $dueTaskBuilder
            ->setDateDueTask(new \DateTimeImmutable($startDate))
            ->setStartHour($startHour)
            ->setEndHour($endHour)
            ->setAssignment($assignment)
            ->build();

        $this->em->persist($dueTask);
        $this->em->flush();

        return new JsonResponse(['status' => 'DueTask crée'], JsonResponse::HTTP_CREATED);

    }

    #[Route(
        path: '/ajax/calendar/updateDueTask/{id}',
        name: 'updateDueTask',
        methods: ['PATCH'],
    )]
    public function updateDueTask(Request $request): Response
    {

        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(['error' => 'Invalid request type'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $datas = json_decode($request->getContent());
        $dueTask = $this->dueTaskRepository->find($datas->id);

        if (!$dueTask) {
            return $this->redirectToRoute('dashboard');
        }

        $user = $this->security->getUser();

        list($startDate, $startHourStr) = $this->dateService->extractDate($datas->start);
        $startHour = new \DateTimeImmutable($startHourStr);

        list($endDate, $endHourStr) = $this->dateService->extractDate($datas->end);
        $endHour = new \DateTimeImmutable($endHourStr);

        $dueTaskBuilder = new DueTaskBuilder();
        $dueTaskBuilder
            ->setDateDueTask(new \DateTimeImmutable($startDate))
            ->setStartHour($startHour)
            ->setEndHour($endHour)
            ->setComment($datas->description);
        $dueTask = $dueTaskBuilder->update($dueTask);

        $this->em->persist($dueTask);
        $this->em->flush();

        return new JsonResponse(['status' => 'DueTask update'], JsonResponse::HTTP_OK);

    }

    #[Route(
        path: '/ajax/calendar/deleteDueTask/{id}',
        name: 'deleteDueTask',
        methods: ['DELETE'],
    )]
    public function deleteDueTask(
        ?DueTask    $dueTask,
        Request     $request,
    ): Response {

        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(['error' => 'Invalid request type'], JsonResponse::HTTP_BAD_REQUEST);
        }
        if (!$dueTask) {
            return new JsonResponse(['status' => 'DueTask not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $this->dueTaskRepository->remove($dueTask, true);
        return new JsonResponse(['status' => 'DueTask deleted'], JsonResponse::HTTP_OK);
    }
}
