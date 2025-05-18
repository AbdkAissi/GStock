<?php
// src/Controller/Admin/ApiController.php

namespace App\Controller\Admin;

use App\Repository\CommandeAchatRepository;
use App\Repository\CommandeVenteRepository;
use App\Repository\ClientRepository;
use App\Repository\FournisseurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    private CommandeAchatRepository $commandeAchatRepository;
    private CommandeVenteRepository $commandeVenteRepository;
    private ClientRepository $clientRepository;
    private FournisseurRepository $fournisseurRepository;

    public function __construct(
        CommandeAchatRepository $commandeAchatRepository,
        CommandeVenteRepository $commandeVenteRepository,
        ClientRepository $clientRepository,
        FournisseurRepository $fournisseurRepository
    ) {
        $this->commandeAchatRepository = $commandeAchatRepository;
        $this->commandeVenteRepository = $commandeVenteRepository;
        $this->clientRepository = $clientRepository;
        $this->fournisseurRepository = $fournisseurRepository;
    }

    #[Route('/admin/api/commandes/client/{clientId}', name: 'api_commandes_client', methods: ['GET'])]
    public function getCommandesClient(int $clientId): JsonResponse
    {
        $commandes = $this->commandeVenteRepository->findBy([
            'client' => $clientId,
            'etat' => 'valide',
        ]);

        $data = array_map(function ($commande) {
            return [
                'id' => $commande->getId(),
                'label' => 'Commande #' . $commande->getId() . ' du ' . $commande->getDate()->format('d/m/Y'),
            ];
        }, $commandes);

        return $this->json($data);
    }

    #[Route('/admin/api/commandes/fournisseur/{fournisseurId}', name: 'api_commandes_fournisseur', methods: ['GET'])]
    public function getCommandesFournisseur(int $fournisseurId): JsonResponse
    {
        $commandes = $this->commandeAchatRepository->findBy([
            'fournisseur' => $fournisseurId,
            'etat' => 'valide',
        ]);

        $data = array_map(function ($commande) {
            return [
                'id' => $commande->getId(),
                'label' => 'Commande #' . $commande->getId() . ' du ' . $commande->getDate()->format('d/m/Y'),
            ];
        }, $commandes);

        return $this->json($data);
    }

    #[Route('/admin/api/reste-a-payer/vente/{id}', name: 'api_reste_a_payer_vente', methods: ['GET'])]
    public function resteAPayerVente(int $id): JsonResponse
    {
        $commande = $this->commandeVenteRepository->find($id);

        if (!$commande) {
            return $this->json(['error' => 'Commande non trouvée'], 404);
        }

        $totalCommande = $commande->getTotalCommande();
        $montantPaye = 0;

        foreach ($commande->getPaiements() as $paiement) {
            $montantPaye += $paiement->getMontant();
        }

        $resteAPayer = max($totalCommande - $montantPaye, 0);

        return $this->json([
            'totalCommande' => $totalCommande,
            'montantPaye' => $montantPaye,
            'resteAPayer' => $resteAPayer,
        ]);
    }

    #[Route('/admin/api/reste-a-payer/achat/{id}', name: 'api_reste_a_payer_achat', methods: ['GET'])]
    public function resteAPayerAchat(int $id): JsonResponse
    {
        $commande = $this->commandeAchatRepository->find($id);

        if (!$commande) {
            return $this->json(['error' => 'Commande non trouvée'], 404);
        }

        $totalCommande = $commande->getTotalCommande();
        $montantPaye = 0;

        foreach ($commande->getPaiements() as $paiement) {
            $montantPaye += $paiement->getMontant();
        }

        $resteAPayer = max($totalCommande - $montantPaye, 0);

        return $this->json([
            'totalCommande' => $totalCommande,
            'montantPaye' => $montantPaye,
            'resteAPayer' => $resteAPayer,
        ]);
    }

    #[Route('/admin/api/clients', name: 'api_liste_clients', methods: ['GET'])]
    public function getClients(): JsonResponse
    {
        $clients = $this->clientRepository->findAll();

        $data = array_map(function ($client) {
            return [
                'id' => $client->getId(),
                'nom' => $client->getNom(),
            ];
        }, $clients);

        return $this->json($data);
    }

    #[Route('/admin/api/fournisseurs', name: 'api_liste_fournisseurs', methods: ['GET'])]
    public function getFournisseurs(): JsonResponse
    {
        $fournisseurs = $this->fournisseurRepository->findAll();

        $data = array_map(function ($fournisseur) {
            return [
                'id' => $fournisseur->getId(),
                'nom' => $fournisseur->getNom(),
            ];
        }, $fournisseurs);

        return $this->json($data);
    }
}
