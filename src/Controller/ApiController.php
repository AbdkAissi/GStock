<?php
// src/Controller/ApiController.php

namespace App\Controller;

use App\Entity\CommandeAchat;
use App\Entity\CommandeVente;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class ApiController extends AbstractController
{
    #[Route('/admin/api/reste-a-payer/{type}/{id}', name: 'api_reste_a_payer')]
    public function getResteAPayer(string $type, int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $repository = null;
        $entityClass = null;

        if ($type === 'vente') {
            $repository = $entityManager->getRepository(CommandeVente::class);
            $entityClass = CommandeVente::class;
        } elseif ($type === 'achat') {
            $repository = $entityManager->getRepository(CommandeAchat::class);
            $entityClass = CommandeAchat::class;
        } else {
            return new JsonResponse(['error' => 'Type de commande invalide'], 400);
        }

        $commande = $repository->find($id);

        if (!$commande) {
            return new JsonResponse(['error' => 'Commande non trouvée'], 404);
        }

        $totalCommande = $commande->getTotalCommande();
        $montantPaye = array_reduce(
            $commande->getPaiements()->toArray(),
            fn(float $total, $p) => $total + $p->getMontant(),
            0
        );

        $resteAPayer = max($totalCommande - $montantPaye, 0);

        return new JsonResponse([
            'id' => $commande->getId(),
            'totalCommande' => $totalCommande,
            'montantPaye' => $montantPaye,
            'resteAPayer' => $resteAPayer
        ]);
    }
}
