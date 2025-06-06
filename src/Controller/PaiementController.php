<?php
// src/Controller/PaiementController.php

namespace App\Controller;

use App\Repository\PaiementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PaiementController extends AbstractController
{
    /**
     * @Route("/paiements", name="paiement_liste")
     */
    public function liste(PaiementRepository $paiementRepository): Response
    {
        // Récupère tous les paiements
        $paiements = $paiementRepository->findAll();

        // Retourne la réponse en utilisant le template 'paiement/liste.html.twig'
        return $this->render('paiement/liste.html.twig', [
            'paiements' => $paiements,
        ]);
    }
}
