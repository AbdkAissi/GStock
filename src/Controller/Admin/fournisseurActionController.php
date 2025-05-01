<?php
// src/Controller/Admin/ClientActionController.php

namespace App\Controller\Admin;

use App\Entity\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\RequestStack;

class fournisseurActionController extends AbstractController
{
    #[Route('/admin/client/{id}/sonner', name: 'admin_client_sonner')]
    public function sonner(Client $client, RequestStack $requestStack): Response
    {
        // Simule une notification (à adapter selon ton besoin réel)
        $this->addFlash('info', sprintf('🔔 Le client "%s" a été sonné avec succès !', $client->getNom()));

        // Redirige vers la page précédente
        $referer = $requestStack->getCurrentRequest()->headers->get('referer');
        return $this->redirect($referer ?? $this->generateUrl('admin'));
    }
}
