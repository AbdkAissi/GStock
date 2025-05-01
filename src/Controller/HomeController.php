<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]  // Nom de route standardisé
    public function index(): RedirectResponse
    {
        // Vérifie si l'utilisateur est connecté
        if ($this->getUser()) {
            // Redirige vers le dashboard ADMIN si rôle ADMIN
            if ($this->isGranted('ROLE_ADMIN')) {
                return $this->redirectToRoute('admin_dashboard');
            }
            // Sinon vers le dashboard utilisateur normal
            return $this->redirectToRoute('app_dashboard');
        }

        // Redirection vers la page de login si non connecté
        return $this->redirectToRoute('app_login');
    }
}
