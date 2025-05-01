<?php

namespace App\Controller;

use App\Entity\Produit;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ProduitController extends AbstractController
{
    #[Route('/produit/{id}/prix', name: 'produit_prix', methods: ['GET'])]
    public function prix(Produit $produit): JsonResponse
    {
        // Vérifier que le produit existe
        if (!$produit) {
            return $this->json([
                'error' => 'Produit non trouvé'
            ], 404);
        }

        // Renvoie à la fois prixVente et prixAchat
        return $this->json([
            'prixAchat' => $produit->getPrixAchat(),
            'prixVente' => $produit->getPrixVente()
        ]);
    }
}
