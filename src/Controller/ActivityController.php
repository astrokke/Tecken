<?php

namespace App\Controller;

use App\Entity\Activity;
use App\Entity\Assignment;
use App\Entity\Task;
use App\Entity\TypeOfTask;
use App\Entity\User;
use App\Form\ActivityFormType;
use App\Form\AssignmentFormType;
use App\Form\TaskFormType;
use App\Form\TypeOfTaskFormType;
use App\Repository\AssignmentRepository;
use App\Repository\TaskRepository;
use App\Repository\TypeOfTaskRepository;
use App\Service\FormHandler;
use App\Service\TaskService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ActivityRepository;
use App\Repository\StateRepository;
use App\Repository\UserRepository;
use App\Service\ActivityProgressService;
use App\Service\ActivityService;
use App\Service\CollaboratorService;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/activity')]
class ActivityController extends AbstractController
{
    public function __construct(
        private ActivityService         $activityService,
        private UserRepository          $userRepository,
        private TypeOfTaskRepository    $totRepository,
    ) {

        $this->userRepository = $userRepository;
    }

    #[Route(
        path: '/{id<\d+>}',
        name: 'viewActivity',
        methods: ['GET'],
    )]
    public function viewActivity(?Activity $activity): Response
    {
        if (!$activity) {
            $this->addFlash('warning', 'Activité non existante !');
            return $this->render('dashboard');
        }

        $activityData = $this->activityService->getActivityData($activity);


        return $this->render('partial/activity/_card.html.twig', [
            'title' => 'Détails de l\'activité',
            'activityData' => $activityData,
        ]);
    }

    #[Route(
        path: '/tasks/{id<\d+>}',
        name: 'viewActivityTasks',
        methods: ['GET'],
    )]
    public function viewActivityTasks(
        ?Activity       $activity,
        Request         $request,
        TaskService     $taskService,
    ): Response {
        if (!$activity) {
            $this->addFlash('warning', 'Activité non existante !');
            return $this->render('dashboard');
        }
        $activityData = $this->activityService->getActivityData($activity);
        $page = $request->query->getInt('page', 1);
        $paginationDatas = $taskService->getPaginatedTasks($activity, $page);


        return $this->render('partial/activity/_tasks_list.html.twig', [
            "activity" => $activity,
            'activityData' => $activityData,
            'allCollaborators' => $this->userRepository->findAll(),
            'tasks' => $paginationDatas['tasks'],
            'currentPage' => $paginationDatas['currentPage'],
            'totalPages' => $paginationDatas['totalPages'],
        ]);
    }

    #[Route(
        path: '/',
        name: 'viewActivities',
        methods: ['GET'],
    )]
    public function viewActivities(): Response
    {
        $activitiesData = $this->activityService->getAllActivitiesData();

        return $this->render('pages/activities.html.twig', [
            'title' => 'Toutes les activités',
            'activities' => $activitiesData['activities'],
            'allCollaborators' => $activitiesData['allCollaborators'],
            'taskTypes' => $activitiesData['taskTypes'],
            'taskStates' => $activitiesData['taskStates'],

        ]);
    }

    #[Route(
        path: '/add',
        name: 'addActivity',
        methods: ['GET', 'POST'],
    )]
    public function addActivity(
        Request         $request,
        FormHandler     $formHandler,
    ): Response {
        $activity = new Activity();
        $form = $this->createForm(ActivityFormType::class, $activity, [
            'action' => $this->generateUrl('addActivity')
        ]);
        if ($formHandler->handleForm($form, $request, true)) {
            return $this->redirectToRoute('viewActivities', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('partial/activity/_form.html.twig', [
            'title' => 'Création d\'une nouvelle activité !',
            'form' => $form,
        ]);
    }

    #[Route(
        path: '/edit/{id}',
        name: 'editActivity',
        methods: ["GET", "POST"],
    )]
    public function updateActivity(
        Request                 $request,
        Activity                $activity,
        FormHandler    $formHandler,
    ): Response {
        if (!$activity) {
            return $this->redirectToRoute('viewActivities');
        }

        $form = $this->createForm(ActivityFormType::class, $activity, [
            'action' => $this->generateUrl('editActivity', ["id" => $activity->getId()]),
        ]);
        if ($formHandler->handleForm($form, $request, true)) {
            return $this->redirectToRoute('viewActivities');
        }

        return $this->render('partial/activity/_form.html.twig', [
            'title' => 'Mise à jour d\'une activité ',
            'form' => $form->createView(),
            'activity' => $activity,
        ]);
    }

    #[Route(
        path: '/delete/{id}',
        name: 'deleteActivity',
    )]
    public function deleteActivity(
        Activity                $activity,
        EntityManagerInterface  $entityManager
    ): Response {

        if (!$activity) {
            $this->addFlash('warning', 'Activité non existante');
            return $this->redirectToRoute('dashboard');
        }
        $entityManager->remove($activity);
        $entityManager->flush();

        $activitiesData = $this->activityService->getAllActivitiesData();

        return $this->render('pages/activities.html.twig', [
            'title' => 'Toutes les activités',
            'activities' => $activitiesData['activities'],
            'allCollaborators' => $activitiesData['allCollaborators'],
            'taskTypes' => $activitiesData['taskTypes'],
            'taskStates' => $activitiesData['taskStates'],

        ]);
    }

    #[Route(
        path: '/addTask/{id}',
        name: 'addTask',
        methods: ["GET", "POST"],
    )]
    public function addTask(
        Request     $request,
        FormHandler $formHandler,
        ?Activity   $activity = null,
    ): Response {
        if (!$activity) {
            return $this->redirectToRoute('viewActivities');
        }
        $form = $this->createForm(TaskFormType::class, null, [
            'action' => $this->generateUrl('addTask', ["id" => $activity->getId()]),
        ]);
        if ($formHandler->handleTaskForm($form, $request, $activity)) {
            return $this->redirectToRoute('viewActivityTasks', ["id" => $activity->getId()]);
        }

        return $this->render('partial/task/_form.html.twig', [
            'title' => $activity ? 'Ajouter une tâche pour ' . $activity->getName() : 'Ajouter une tâche',
            'form' => $form,
            'activity' => $activity,
        ]);
    }

    #[Route(
        path: '/edit-task/{id<\d+>}',
        name: 'editTask',
        methods: ['GET', 'POST']
    )]
    public function editTask(
        Request $request,
        ?Task $task,
        FormHandler $formHandler,
        EntityManagerInterface $em
    ): Response {
        if (!$task) {
            return $this->redirectToRoute('viewActivities');
        }

        $activity = $task->getActivity();
        if (!$activity) {
            return $this->redirectToRoute('viewActivities');
        }

        $form = $this->createForm(TaskFormType::class, $task, [
            'action' => $this->generateUrl('editTask', ["id" => $task->getId()]),
        ]);

        if ($formHandler->handleTaskForm($form, $request, $activity)) {
            return $this->redirectToRoute('viewActivityTasks', ["id" => $activity->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('partial/task/_form.html.twig', [
            'title' => 'Mise à jour d\'une tâche',
            'form' => $form,
            'activity' => $activity,
        ]);
    }


    #[Route(
        path: '/delete-task/{id}',
        name: 'deleteTask',
    )]
    public function deleteTask(
        ?Task  $task,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        ActivityService $activityService,
    ): Response {
        if (!$task) {
            return $this->redirectToRoute('viewActivities');
        }
        $activity = $task->getActivity();
        $activityData = $activityService->getActivityData($activity);
        try {
            $entityManager->remove($task);
            $entityManager->flush();
            return $this->redirectToRoute('viewActivityTasks', [
                'id' => $activity->getId(),
                Response::HTTP_SEE_OTHER
            ]);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de la suppression de la tâche.');

            return $this->redirectToRoute('viewActivities');
        }


        return $this->render('pages/activities.html.twig', [
            'task' => $task,
            'activity' => $activity,
            'activityData' => $activityData,
            'allCollaborators' => $userRepository->findAll(),
        ]);
    }

    #[Route(
        path: '/add-assignment/{id}',
        name: 'addAssignment',
        methods: ['POST']
    )]
    public function addAssignment(
        Request $request,
        Task $task,
        UserRepository $userRepository,
        EntityManagerInterface $em,
        ActivityService $activityService,
    ): Response {
        $collaboratorId = $request->request->get('collaborator');
        if (!$collaboratorId) {
            return $this->redirectToRoute('viewActivities');
        }

        $collaborator = $userRepository->find($collaboratorId);
        if (!$collaborator) {
            return $this->redirectToRoute('viewActivities');
        }

        $assignment = new Assignment();
        $assignment->setTask($task);
        $assignment->setCollaborator($collaborator);
        $em->persist($assignment);
        $em->flush();

        $activity = $task->getActivity();
        $activityData = $activityService->getActivityData($task->getActivity());

        return $this->render('partial/task/_collaborator_list.html.twig', [
            'activity' => $activity,
            'activityData' => $activityData,
            'task' => $task,
            'taskCollabs' => $activityData['taskCollabs'],
            'allCollaborators' => $userRepository->findAll(),
        ]);
    }

    #[Route(
        path: '/edit-assignment/{user}{task}',
        name: 'editAssignment',
        methods: ['GET', 'POST']
    )]
    public function editAssignment(
        Request     $request,
        Assignment  $assignment,
        FormHandler $formHandler,
    ): Response {
        if ($assignment === null) {
            return $this->redirectToRoute('viewAssignment');
        }
        $form = $this->createForm(AssignmentFormType::class, $assignment);
        if ($formHandler->handleForm($form, $request, true)) {
            return $this->redirectToRoute('viewAssignment');
        }
        return $this->render('assignment/add.html.twig', [
            'title' => 'Modifier un assignement !',
            'form' => $form,
        ]);
    }

    #[Route(
        path: '/delete-assignment/{userId}/{taskId}',
        name: 'deleteAssignment',
        methods: ['POST']
    )]
    public function deleteAssignment(
        AssignmentRepository    $assignmentRepository,
        int                     $userId,
        int                     $taskId,
        ActivityService $activityService,
        TaskRepository $taskRepository,
        UserRepository $userRepository,
    ): Response {
        $assignment = $assignmentRepository->findByUserAndTask($userId, $taskId);
        if (!$assignment) {
            return $this->redirectToRoute('viewActivities');
        }
        $assignmentRepository->remove($assignment, true);

        $task = $taskRepository->find($taskId);
        $activityData = $activityService->getActivityData($task->getActivity());

        return $this->render('partial/task/_collaborator_list.html.twig', [
            'task' => $task,
            'activity' => $task->getActivity(),
            'activityData' => $activityData,
            'taskCollabs' => $activityData['taskCollabs'],
            'allCollaborators' => $userRepository->findAll(),
        ]);
    }

    #[Route(
        path: '/addTypeOfTask',
        name: 'addTypeOfTask',
        methods: ['GET', 'POST'],
    )]
    public function addTypeOfTask(
        Request         $request,
        FormHandler     $formHandler,
    ): Response {
        $tot = new TypeOfTask();
        $form = $this->createForm(TypeOfTaskFormType::class, $tot, [
            'action' => $this->generateUrl('addTypeOfTask')
        ]);
        if ($formHandler->handleForm($form, $request, true)) {
            return $this->redirectToRoute('viewActivities', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('partial/activity/_form_tot.html.twig', [
            'title' => 'Création d\'un nouveau type de tâche !',
            'form' => $form,
        ]);
    }

    #[Route(
        path: '/viewTypeOfTask',
        name: 'viewTypeOfTask',
        methods: ['GET'],
    )]
    public function viewTypeOfTask(): Response
    {

        $tots = $this->totRepository->findAll();
        $taskTypes = array_map(function ($tot) {
            return [
                'entity' => $tot,
                'canBeDeleted' => $this->totRepository->canBeDeleted($tot),
            ];
        }, $tots);
        return $this->render('partial/type_of_task/_tot.html.twig', [
            'tots' => $taskTypes,
        ]);
    }

    #[Route(
        path: '/editTypeOfTask/{id}',
        name: 'editTypeOfTask',
        methods: ["GET", "POST"],
    )]
    public function updateTypeOfTask(
        Request     $request,
        TypeOfTask  $tot,
        FormHandler $formHandler,
    ): Response {
        if (!$tot) {
            return $this->redirectToRoute('viewActivities');
        }

        $form = $this->createForm(TypeOfTaskFormType::class, $tot, [
            'action' => $this->generateUrl('editTypeOfTask', ["id" => $tot->getId()]),
        ]);
        if ($formHandler->handleForm($form, $request, true)) {
            return $this->redirectToRoute('viewActivities');
        }

        return $this->render('partial/activity/_form_edit_tot.html.twig', [
            'title' => 'Mise à jour d\'un type de tâche',
            'form' => $form->createView(),
            'tot' => $tot,
        ]);
    }

    #[Route(
        path: '/deleteTypeOfTask/{id}',
        name: 'deleteTypeOfTask',
    )]
    public function deleteTypeOfTask(
        TypeOfTask              $tot,
        EntityManagerInterface  $entityManager
    ): Response {

        if (!$tot) {
            $this->addFlash('warning', 'Type de tâche non existant');
            return $this->redirectToRoute('viewActivities');
        }
        if(!$this->totRepository->canBeDeleted($tot)) {
            $this->addFlash('danger', 'Ce type de tâche est utilisé et ne peut pas être supprimé.');
            return $this->redirectToRoute('viewActivities');
        }

        $entityManager->remove($tot);
        $entityManager->flush();
        $this->addFlash('success', 'Type de tâche supprimé avec succès');


        $activitiesData = $this->activityService->getAllActivitiesData();

        return $this->render('pages/activities.html.twig', [
            'title' => 'Toutes les activités',
            'activities' => $activitiesData['activities'],
            'allCollaborators' => $activitiesData['allCollaborators'],
            'taskTypes' => $activitiesData['taskTypes'],
            'taskStates' => $activitiesData['taskStates'],

        ]);

    }


}
