<?php
// src/Controller/Admin/ApiController.php
namespace App\Controller\Admin;

use App\Repository\CommandeAchatRepository;
use App\Repository\CommandeVenteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ClientRepository;
use App\Repository\FournisseurRepository;

class ApiController extends AbstractController
{
    #[Route('/admin/api/commandes/achat/{id}', name: 'api_commandes_achat_par_fournisseur')]
    public function commandesAchatParFournisseur(int $id, CommandeAchatRepository $repo): JsonResponse
    {
        $commandes = $repo->findBy(['fournisseur' => $id]);
        return $this->json(array_map(fn($c) => [
            'id' => $c->getId(),
            'label' => 'Commande #' . $c->getId(),
        ], $commandes));
    }

    #[Route('/admin/api/commandes/vente/{id}', name: 'api_commandes_vente_par_client')]
    public function commandesVenteParClient(int $id, CommandeVenteRepository $repo): JsonResponse
    {
        $commandes = $repo->findBy(['client' => $id]);
        return $this->json(array_map(fn($c) => [
            'id' => $c->getId(),
            'label' => 'Commande #' . $c->getId(),
        ], $commandes));
    }
    #[Route('/admin/api/commandes/{type}/{id}', name: 'admin_api_commandes', methods: ['GET'])]
    public function getCommandesParDestinataire(string $type, int $id, CommandeAchatRepository $achatRepo, CommandeVenteRepository $venteRepo): JsonResponse
    {
        if (empty($commandes)) {
            return $this->json(['error' => 'Aucune commande trouvée pour ce ' . $type], 404);
        }

        if ($type === 'client') {
            $commandes = $venteRepo->findBy(['client' => $id]);
        } elseif ($type === 'fournisseur') {
            $commandes = $achatRepo->findBy(['fournisseur' => $id]);
        } else {
            return $this->json([], 400);
        }

        $data = array_map(fn($cmd) => [
            'id' => $cmd->getId(),
            'label' => 'Commande #' . $cmd->getId() . ' du ' . $cmd->getDate()->format('d/m/Y'),
        ], $commandes);

        return $this->json($data);
    }

    #[Route('/admin/api/reste-a-payer/vente/{id}', name: 'api_reste_a_payer_vente')]
    public function resteAPayerVente($id, CommandeVenteRepository $repo): JsonResponse
    {
        $commande = $repo->find($id);

        if (!$commande) {
            return $this->json(['error' => 'Commande non trouvée'], 404);
        }

        // Calcul du montant total de la commande
        $totalCommande = $commande->getTotalCommande();

        // Calcul du montant déjà payé
        $montantPaye = 0;
        foreach ($commande->getPaiements() as $paiement) {
            $montantPaye += $paiement->getMontant();
        }

        // Calcul du reste à payer
        $resteAPayer = max($totalCommande - $montantPaye, 0);

        return $this->json([
            'totalCommande' => $totalCommande,
            'montantPaye' => $montantPaye,
            'resteAPayer' => $resteAPayer
        ]);
    }

    #[Route('/admin/api/reste-a-payer/achat/{id}', name: 'api_reste_a_payer_achat')]
    public function resteAPayerAchat($id, CommandeAchatRepository $repo): JsonResponse
    {
        $commande = $repo->find($id);

        if (!$commande) {
            return $this->json(['error' => 'Commande non trouvée'], 404);
        }

        // Calcul du montant total de la commande
        $totalCommande = $commande->getTotalCommande();

        // Calcul du montant déjà payé
        $montantPaye = 0;
        foreach ($commande->getPaiements() as $paiement) {
            $montantPaye += $paiement->getMontant();
        }

        // Calcul du reste à payer
        $resteAPayer = max($totalCommande - $montantPaye, 0);

        return $this->json([
            'totalCommande' => $totalCommande,
            'montantPaye' => $montantPaye,
            'resteAPayer' => $resteAPayer
        ]);
    }
    //************
    #[Route('/admin/api/clients', name: 'api_liste_clients')]
    public function getClients(ClientRepository $repo): JsonResponse
    {
        $clients = $repo->findAll();
        return $this->json(array_map(fn($c) => [
            'id' => $c->getId(),
            'nom' => $c->getNom(),
        ], $clients));
    }

    #[Route('/admin/api/fournisseurs', name: 'api_liste_fournisseurs')]
    public function getFournisseurs(FournisseurRepository $repo): JsonResponse
    {
        $fournisseurs = $repo->findAll();
        return $this->json(array_map(fn($f) => [
            'id' => $f->getId(),
            'nom' => $f->getNom(),
        ], $fournisseurs));
    }
}
