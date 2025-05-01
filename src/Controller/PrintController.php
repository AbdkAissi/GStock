<?php

namespace App\Controller;

use App\Entity\CommandeVente;
use App\Entity\CommandeAchat;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PrintController extends AbstractController
{
    // Route pour imprimer une commande de vente
    #[Route('/commande-vente/{id}/imprimer', name: 'commande_vente_imprimer')]
    public function imprimerVente(CommandeVente $commande): Response
    {
        return $this->render('print/commande_vente.html.twig', [
            'commande' => $commande,
        ]);
    }

    // Route pour imprimer une commande d'achat
    #[Route('/commande-achat/{id}/imprimer', name: 'commande_achat_imprimer')]
    public function imprimerAchat(CommandeAchat $commande): Response
    {
        // Logique d'impression pour la commande d'achat
        return $this->render('print/commande_achat.html.twig', [
            'commandeAchat' => $commande,
        ]);
    }
}
