<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LoginController extends AbstractController
{
    #[Route('/', name: 'login')]
    public function login(): Response
    {
        return $this->render('pages/login.html.twig', [
            'controller_name' => 'LoginController',
        ]);
    }
}
