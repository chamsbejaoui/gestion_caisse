<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Security;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        // Vérifier si l'utilisateur est connecté
        if (!$this->getUser()) {
            // Rediriger vers la page de connexion pour les utilisateurs non connectés
            return $this->redirectToRoute('app_login');
        }
        
        // Vérifier si l'utilisateur est un administrateur
        if ($this->isGranted('ROLE_ADMIN')) {
            // Rediriger vers la gestion des utilisateurs
            return $this->redirectToRoute('app_user_index');
        }
        
        // Afficher la page d'accueil pour les utilisateurs normaux
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
}