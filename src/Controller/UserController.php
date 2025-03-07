<?php

namespace App\Controller;

use App\Entity\Assignment;
use App\Entity\DueTask;
use App\Entity\User;
use App\Form\UserFormType;
use App\Repository\UserRepository;
use App\Service\FileUploader;
use App\Service\FormHandler;
use App\Service\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/user")]
class UserController extends AbstractController
{
    #[Route('/', name: 'viewUsers', methods: ['GET'])]
    public function index(UserRepository $UserRepository): Response
    {
        $users = $UserRepository->findAllOrdered();
        return $this->render('pages/users.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/add', name: 'addUser', methods: ['GET', 'POST'])]
    public function addUser(Request $request, FormHandler $formHandler): Response
    {
        $user = new User();

        $form = $this->createForm(UserFormType::class, $user);
        if ($formHandler->handleForm($form, $request, true)) {
            return $this->redirectToRoute('viewUsers');
        }
        return $this->render('partial/_form.html.twig', [
            'title' => 'Ajouter un utilisateur',
            'form' => $form,
            'user' => $user,
        ]);
    }

    #[Route('/edit/{id}', name: 'editUser', methods: ['GET', 'POST'])]
    public function editUser(User $user, Request $request, FormHandler $formHandler): Response
    {
        if (!$user) {
            return $this->redirectToRoute('viewUsers');
        }
        $form = $this->createForm(UserFormType::class, $user);
        if ($formHandler->handleForm($form, $request, true)) {
            return $this->redirectToRoute('viewUsers');
        }

        return $this->render('partial/_form.html.twig', [
            'title' => 'Modifier le profile',
            'form' => $form,
            'user' => $user,
        ]);
    }

    #[Route('/delete/{id}', name: 'deleteUser')]
    public function deleteUser(
        User $user,
        UserManager $userManager
    ): Response {
        if (!$user) {
            return $this->redirectToRoute('viewUsers');
        }

        if ($userManager->deleteUser($user)) {
            $this->addFlash('success', 'Utilisateur supprimé avec succès');
        } else {
            $this->addFlash('error', 'Erreur lors de la suppression de l\'utilisateur');
        }

        return $this->redirectToRoute('viewUsers');
    }
}
