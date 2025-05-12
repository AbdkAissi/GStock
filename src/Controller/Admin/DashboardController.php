<?php

namespace App\Controller\Admin;

use App\Entity\Client;
use App\Entity\CommandeAchat;
use App\Entity\CommandeVente;
use App\Entity\Fournisseur;
use App\Entity\Produit;
use App\Entity\Paiement;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ProduitRepository;
use App\Repository\ClientRepository;
use App\Repository\CommandeVenteRepository;
use App\Repository\CommandeAchatRepository;
use App\Controller\Admin\CommandeVenteCrudController;

class DashboardController extends AbstractDashboardController
{
    // Injection des repositories nécessaires pour accéder aux données depuis la base
    public function __construct(
        private ProduitRepository $produitRepository,
        private ClientRepository $clientRepository,
        private CommandeVenteRepository $commandeVenteRepository,
        private CommandeAchatRepository $commandeAchatRepository
    ) {}

    // Route principale de l'admin : /admin
    #[Route('/admin', name: 'app_dashboard')]
    public function index(): Response
    {
        // Récupération du nombre total de produits, clients, commandes de vente et commandes d'achat
        $produitsCount = $this->produitRepository->count([]);
        $clientsCount = $this->clientRepository->count([]);
        $commandesvCount = $this->commandeVenteRepository->count([]);
        $commandesaCount = $this->commandeAchatRepository->count([]);

        // Affichage du tableau de bord avec les statistiques dans un template Twig
        return $this->render('admin/empty_dashboard.html.twig', [
            'totalProduits' => $produitsCount,
            'totalClients' => $clientsCount,
            'totalCommandesVente' => $commandesvCount,
            'totalCommandesAchat' => $commandesaCount
        ]);
    }

    // Configuration générale du tableau de bord (titre, favicon)
    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('<i class="fas fa-warehouse"></i> Gestion de Stock') // Titre affiché dans l’admin
            ->setFaviconPath('favicon.ico'); // Icône de l’onglet navigateur
    }

    // Configuration du menu de navigation dans l'interface EasyAdmin
    public function configureMenuItems(): iterable
    {
        // Lien vers le tableau de bord principal
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        // Sous-menu pour la partie commerciale : Produits et Clients
        yield MenuItem::subMenu('Gestion Commerciale', 'fas fa-bars')->setSubItems([
            MenuItem::linkToCrud('Produits', 'fas fa-box', Produit::class),
            MenuItem::linkToCrud('Clients', 'fas fa-users', Client::class),
        ]);

        // Sous-menu pour la logistique : Fournisseurs, Commandes d’achat et de vente
        yield MenuItem::subMenu('Gestion Logistique', 'fas fa-bars')->setSubItems([
            MenuItem::linkToCrud('Fournisseurs', 'fas fa-truck', Fournisseur::class),
            MenuItem::linkToCrud('Commandes Achat', 'fas fa-shopping-cart', CommandeAchat::class),
            MenuItem::linkToCrud('Commandes Vente', 'fas fa-cash-register', CommandeVente::class)
                ->setController(CommandeVenteCrudController::class),
            MenuItem::linkToCrud('Paiements', 'fa fa-money-bill', Paiement::class),
        ]);
    }

    // Ajout de fichiers JS et CSS personnalisés pour l'administration
    public function configureAssets(): Assets
    {
        return parent::configureAssets()
            ->addWebpackEncoreEntry('admin') // Entrée Webpack principale pour l’admin
            ->addJsFile('build/auto-prix-vente.js') // Utilise le chemin correct pour Webpack
            ->addJsFile('build/auto-prix-achat.js') // Utilise le chemin correct pour Webpack
            ->addCssFile('build/admin.css'); // CSS via Webpack
    }
}
