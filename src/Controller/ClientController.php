<?php

namespace App\Controller;

use App\Entity\Activity;
use App\Entity\Assignment;
use App\Entity\Client;
use App\Entity\Interlocutor;
use App\Entity\Task;
use App\Form\ClientFormType;
use App\Form\InterlocutorFormType;
use App\Form\TaskFormType;
use App\Repository\ActivityRepository;
use App\Repository\AssignmentRepository;
use App\Repository\ClientRepository;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use App\Service\ActivityService;
use App\Service\DateConvertService;
use App\Service\FileUploader;
use App\Service\FormHandler;
use App\Service\TaskService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/client', methods: ['GET', 'POST'])]
class ClientController extends AbstractController
{
    public function __construct(
        private ActivityService     $activityService,
        private ActivityRepository  $activityRepository,
        private UserRepository      $userRepository,
        private TaskRepository      $taskRepository,
        private TaskService         $taskService,
        private DateConvertService  $dateService,
    ) {}

    #[Route(
        path: '/',
        name: 'viewClients',
        methods: ['GET']
    )]
    public function viewClient(
        ClientRepository    $repository,
        Request             $request,
    ): Response {

        $clients = $repository->findAll();

        return $this->render('pages/clients.html.twig', [
            'title' => 'Liste des clients',
            'clients' => $clients,
            'socialReason' => $request->query->get('socialReason', ''),
        ]);
    }

    #[Route(
        path: "/activity-details/{id}",
        name: "activityDetails",
        methods: ['GET']
    )]
    public function activityDetails(
        Activity $activity
    ): Response {
        $activityData = $this->activityService->getActivityData($activity);
        return $this->render('partial/client/_activity_card.html.twig', [
            'activityData' => $activityData,
        ]);
    }

    #[Route(
        path: '/addClientTask/{id}',
        name: 'addClientTask',
        methods: ["GET", "POST"],
    )]
    public function addClientTask(
        Request     $request,
        FormHandler $formHandler,
        ?Activity   $activity = null,
    ): Response {
        if (!$activity) {
            return $this->redirectToRoute('viewClients');
        }
        $form = $this->createForm(TaskFormType::class, null, [
            'action' => $this->generateUrl('addClientTask', [
                "id" => $activity->getId()
            ]),
        ]);
        if ($formHandler->handleTaskForm($form, $request, $activity)) {
            return $this->redirectToRoute('clientActivityTasks', [
                "id" => $activity->getId()
            ]);
        }

        return $this->render('partial/client/_task_form.html.twig', [
            'title' => $activity ? 'Ajouter une tâche pour ' . $activity->getName() : 'Ajouter une tâche',
            'form' => $form,
            'activity' => $activity,
        ]);
    }

    #[Route(
        path: '/edit-client-task/{id<\d+>}',
        name: 'editClientTask',
        methods: ['GET', 'POST']
    )]
    public function editClientTask(
        Request     $request,
        ?Task       $task,
        FormHandler $formHandler,
    ): Response {
        if (!$task) {
            return $this->redirectToRoute('viewClients');
        }

        $activity = $task->getActivity();
        if (!$activity) {
            return $this->redirectToRoute('viewClients');
        }

        $form = $this->createForm(TaskFormType::class, $task, [
            'action' => $this->generateUrl('editClientTask', [
                "id" => $task->getId()
            ]),
        ]);

        if ($formHandler->handleTaskForm($form, $request, $activity)) {
            return $this->redirectToRoute('clientActivityTasks', [
                "id" => $activity->getId()
            ]);
        }

        return $this->render('partial/client/_task_form_edit.html.twig', [
            'title' => 'Mise à jour d\'une tâche',
            'form' => $form,
            'task' => $task,
            'activity' => $activity,
            'task_id' => $task->getId(),
        ]);
    }

    #[Route(
        path: '/delete-client-task/{id}',
        name: 'deleteClientTask',
    )]
    public function deleteClientTask(
        ?Task                   $task,
        EntityManagerInterface  $entityManager,
    ): Response {
        if (!$task) {
            return $this->redirectToRoute('viewClients');
        }
        $activity = $task->getActivity();
        $activityData = $this->activityService->getActivityData($activity);
        try {
            $entityManager->remove($task);
            $entityManager->flush();
            return $this->redirectToRoute('clientActivityTasks', [
                'id' => $activity->getId(),
                Response::HTTP_SEE_OTHER
            ]);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de la suppression de la tâche.');

            return $this->redirectToRoute('viewClients');
        }


        return $this->render('partial/client/_activity_card.html.twig', [
            'task' => $task,
            'activity' => $activity,
            'activityData' => $activityData,
            'allCollaborators' => $this->userRepository->findAll(),
        ]);
    }

    #[Route(
        path: '/tasks/{id<\d+>}',
        name: 'clientActivityTasks',
        methods: ['GET'],
    )]
    public function clientActivityTasks(
        ?Activity   $activity,
        Request     $request,
        TaskService $taskService,
    ): Response {
        if (!$activity) {
            $this->addFlash('warning', 'Activité non existante !');
            return $this->render('dashboard');
        }
        $activityData = $this->activityService->getActivityData($activity);
        $page = $request->query->getInt('page', 1);
        $paginationDatas = $taskService->getPaginatedTasks($activity, $page);

        return $this->render('partial/client/_activity_tasks_list.html.twig', [
            "activity" => $activity,
            'activityData' => $activityData,
            'allCollaborators' => $this->userRepository->findAll(),
            'tasks' => $paginationDatas['tasks'],
            'currentPage' => $paginationDatas['currentPage'],
            'totalPages' => $paginationDatas['totalPages'],
        ]);
    }

    #[Route(
        path: '/add-assignment-client/{id}',
        name: 'addClientAssignment',
        methods: ['POST']
    )]
    public function addClientAssignment(
        Request                 $request,
        Task                    $task,
        EntityManagerInterface  $em,
    ): Response {
        $collaboratorId = $request->request->get('collaborator');
        if (!$collaboratorId) {
            return $this->redirectToRoute('viewClients');
        }

        $collaborator = $this->userRepository->find($collaboratorId);
        if (!$collaborator) {
            return $this->redirectToRoute('viewClients');
        }

        $assignment = new Assignment();
        $assignment->setTask($task);
        $assignment->setCollaborator($collaborator);
        $em->persist($assignment);
        $em->flush();

        $activity = $task->getActivity();
        $activityData = $this->activityService->getActivityData($task->getActivity());

        return $this->render('partial/client/_task_collaborator_list.html.twig', [
            'activity' => $activity,
            'activityData' => $activityData,
            'task' => $task,
            'taskCollabs' => $activityData['taskCollabs'],
            'allCollaborators' => $this->userRepository->findAll(),
        ]);
    }

    #[Route(
        path: '/delete-assignment-client/{userId}/{taskId}',
        name: 'deleteClientAssignment',
        methods: ['POST']
    )]
    public function deleteClientAssignment(
        AssignmentRepository    $assignmentRepository,
        TaskRepository          $taskRepository,
        int                     $userId,
        int                     $taskId,
    ): Response {
        $assignment = $assignmentRepository->findByUserAndTask($userId, $taskId);
        if (!$assignment) {
            return $this->redirectToRoute('viewClients');
        }
        $assignmentRepository->remove($assignment, true);

        $task = $taskRepository->find($taskId);
        $activityData = $this->activityService->getActivityData($task->getActivity());

        return $this->render('partial/client/_task_collaborator_list.html.twig', [
            'task' => $task,
            'activity' => $task->getActivity(),
            'activityData' => $activityData,
            'taskCollabs' => $activityData['taskCollabs'],
            'allCollaborators' => $this->userRepository->findAll(),
        ]);
    }

    #[Route(
        path: '/add',
        name: 'addClient',
        methods: ['GET', 'POST']
    )]
    public function addClient(
        Request     $request,
        FormHandler $formHandler
    ): Response {
        $client = new Client();
        $form = $this->createForm(ClientFormType::class, $client, [
            'action' => $this->generateUrl('addClient')
        ]);

        if ($formHandler->handleForm($form, $request, true)) {
            return $this->redirectToRoute('viewClients', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('partial/client/_form.html.twig', [
            'title' => 'Ajout de client',
            'form' => $form->createView(),
            'client' => $client,
            'button_label' => 'Ajouter',
        ]);
    }

    #[Route(
        path: '/edit/{id}',
        name: 'editClient',
        methods: ['GET', 'POST']
    )]
    public function editClient(
        Client      $client,
        Request     $request,
        FormHandler $formHandler
    ): Response {
        $form = $this->createForm(ClientFormType::class, $client, [
            'action' => $this->generateUrl('editClient', [
                'id' => $client->getId()
            ]),
        ]);

        if ($formHandler->handleForm($form, $request, true)) {
            return $this->redirectToRoute('viewClients', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('partial/client/_edit_form.html.twig', [
            'form' => $form->createView(),
            'client' => $client,
            'title' => 'Mise à jour du client',
        ]);
    }

    #[Route(
        path: '/delete/{id}',
        name: 'deleteClient',
    )]
    public function deleteClient(
        Client                  $client,
        EntityManagerInterface  $entityManager,
        FileUploader            $fileUploader,
    ): Response {
        if (!$client) {
            $this->addFlash('warning', 'Client non existant !');
            return $this->redirectToRoute('viewClients');
        }

        $entityManager->remove($client);
        $entityManager->flush();

        return $this->redirectToRoute('viewClients');
    }

    #[Route(
        path: '/add-interlocutor/{clientId}',
        name: 'addInterlocutor',
        methods: ['GET', 'POST']
    )]
    public function addInterlocutor(
        Request             $request,
        FormHandler         $formHandler,
        ClientRepository    $clientRepository,
        int                 $clientId,
    ): Response {
        $client = $clientRepository->find($clientId);
        if (!$client) {
            throw $this->createNotFoundException('Client not found');
        }
        $interlocutor = new Interlocutor();
        $interlocutor->setClient($client);
        $form = $this->createForm(InterlocutorFormType::class, $interlocutor, [
            'client' => $client,
            'action' => $this->generateUrl('addInterlocutor', ['clientId' => $clientId]),
            'method' => 'POST',
        ]);

        if ($request->isMethod('POST')) {
            if ($formHandler->handleFormInterlocutor($form, $request, $client)) {
                $this->addFlash('success', 'Interlocuteur ajouté avec succès');
                return $this->redirectToRoute('viewClients', ['id' => $client->getId()]);
            }
        }

        return $this->render('partial/interlocutor/_form.html.twig', [
            'title' => 'Ajout d\'un interlocuteur pour ' . $client->getSocialReason(),
            'form' => $form->createView(),
            'client' => $client,
            'clientId' => $clientId,
        ]);
    }

    #[Route(
        path: '/edit-interlocutor/{id}',
        name: 'editInterlocutor',
        methods: ['GET', 'POST']
    )]
    public function editInterlocutor(
        Request             $request,
        Interlocutor        $interlocutor,
        FormHandler         $formHandler,
        ClientRepository    $repository,
    ): Response {
        $clients = $repository->findAll();
        $form = $this->createForm(InterlocutorFormType::class, $interlocutor, [
            'action' => $this->generateUrl('editInterlocutor', ['id' => $interlocutor->getId()]),
        ]);
        if ($formHandler->handleForm($form, $request, true)) {
            return $this->render('pages/clients.html.twig', [
                'interlocutor' => $interlocutor,
                'title' => 'Modification d\'un interlocuteur',
                'clients' => $clients,
            ]);
        }
        return $this->render('partial/interlocutor/_edit_form.html.twig', [
            'title' => 'Modification d\'un interlocuteur',
            'form' => $form->createView(),
            'interlocutor' => $interlocutor,
            'client' => $interlocutor->getClient(),

        ]);
    }

    #[Route(
        path: '/delete-interlocutor/{id}',
        name: 'deleteInterlocutor',
    )]
    public function deleteInterlocutor(
        Interlocutor            $interlocutor,
        EntityManagerInterface  $entityManager,
        ClientRepository        $clientRepository,
    ): Response {

        if (!$interlocutor) {
            return $this->redirectToRoute('viewClients');
        }

        $clients = $clientRepository->findAll();
        $entityManager->remove($interlocutor);
        $entityManager->flush();

        return $this->redirectToRoute('viewClients', [
            'title' => 'Liste des clients',
            'clients' => $clients,
            'interlocutor' => $interlocutor,
        ]);
    }

    #[Route(
        path: '/statTask/{id}',
        name: 'statTask',
    )]
    public function viewClientStat(
        Security $security,
        Request $request,
        int $id,
    ): Response {
        $user = $security->getUser();
        $monthOffset = $request->query->getInt('monthOffset', 0);
        $search = $request->query->get('search', '');
        $date = (new \DateTimeImmutable())->modify("{$monthOffset} month");
        $activity = $this->activityRepository->find($id);
        if (!$activity) {
            $this->addFlash('warning', 'Activité non existante !');
            return $this->redirectToRoute('dashboard');
        }
        $taskStats = $this->taskService->getUserStatistics($user);
        $tasks = $this->taskService->getClientData($activity, $date->format('n'));
        if ($search) {
            $taskSearch = $this->taskRepository->searchTask($search, 'ASC');
        } else {
            $taskSearch = $tasks;
        }


        return $this->render('partial/client/_clientStat.html.twig', [
            'title' => 'Vue client',
            'user' => $user,
            'countActivity' => $taskStats['countActivity'],
            'percentDone' => $taskStats['percentDone'],
            'tasks' => $taskStats['tasks'],
            'activity' => $activity,
            'monthOffset' => $monthOffset ?? 0,
            'periodLabel' => "en " . ucfirst(sprintf("%s", $this->dateService->getMonthInFrench($date))),
            'tasksDto' => $tasks,
            'taskSearch' => $taskSearch,
            'search' => $search,
        ]);
    }
}
