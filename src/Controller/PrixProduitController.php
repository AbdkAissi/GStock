<?php
// src/Controller/PrixProduitController.php

namespace App\Controller;

use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class PrixProduitController extends AbstractController
{
    #[Route('/produit/{id}/prix', name: 'produit_prix')]
    public function getPrix(int $id, ProduitRepository $repo): JsonResponse
    {
        $produit = $repo->find($id);

        if (!$produit) {
            return new JsonResponse(['prix' => 0], 404);
        }

        return new JsonResponse(['prix' => $produit->getPrixUnitaire()]);
    }
}
