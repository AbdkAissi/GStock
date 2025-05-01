<?php

namespace App\Controller\Admin;

use App\Entity\CommandeVente;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class CommandeController extends AbstractController
{
    #[Route('/admin/commande/{id}/check-stock', name: 'admin_commande_check_stock')]
    public function checkStock(CommandeVente $commande): JsonResponse
    {
        // Parcours chaque ligne de commande dans la commande de vente
        foreach ($commande->getLignesCommandeVente() as $ligne) {
            // Récupère le produit associé à la ligne de commande
            $produit = $ligne->getProduit();

            // Vérifie si le produit existe et si le stock est inférieur au seuil d'alerte
            if ($produit && $produit->getQuantiteStock() < $produit->getSeuilAlerte()) {
                return $this->json([
                    'has_low_stock' => true,  // Indique qu'il y a un problème de stock faible
                    'produit' => $produit->getNom(),  // Nom du produit concerné
                    'quantite_stock' => $produit->getQuantiteStock(),  // Quantité actuelle en stock
                    'seuil_alerte' => $produit->getSeuilAlerte(),  // Seuil d'alerte défini pour le produit
                ]);
            }
        }

        // Si aucun produit n'a un stock faible
        return $this->json(['has_low_stock' => false]);
    }
}
