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
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Controller\Admin\UserCrudController;
use App\Entity\User;

class DashboardController extends AbstractDashboardController
{
    // Injection des repositories nécessaires pour accéder aux données depuis la base
    public function __construct(
        private ProduitRepository $produitRepository,
        private ClientRepository $clientRepository,
        private CommandeVenteRepository $commandeVenteRepository,
        private CommandeAchatRepository $commandeAchatRepository,
        private AdminUrlGenerator $adminUrlGenerator,
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
        // Génère les URLs vers les pages CRUD correspondantes
        $urls = [
            'produits' => $this->adminUrlGenerator->setController(\App\Controller\Admin\ProduitCrudController::class)->generateUrl(),
            'clients' => $this->adminUrlGenerator->setController(\App\Controller\Admin\ClientCrudController::class)->generateUrl(),
            'commandes_vente' => $this->adminUrlGenerator->setController(\App\Controller\Admin\CommandeVenteCrudController::class)->generateUrl(),
            'commandes_achat' => $this->adminUrlGenerator->setController(\App\Controller\Admin\CommandeAchatCrudController::class)->generateUrl(),
        ];
        $ventesParMois = $this->commandeVenteRepository->getNombreVentesParMois();
        $achatParMois = $this->commandeAchatRepository->getNombreAchatParMois();

        // Affichage du tableau de bord avec les statistiques dans un template Twig
        return $this->render('admin/empty_dashboard.html.twig', [
            'totalProduits' => $produitsCount,
            'totalClients' => $clientsCount,
            'totalCommandesVente' => $commandesvCount,
            'totalCommandesAchat' => $commandesaCount,
            'urls' => $urls,
            'ventesParMois' => $ventesParMois,
            'achatsParMois' => $achatParMois,

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
        // Menu principal

        // Section Clients & Fournisseurs
        yield MenuItem::subMenu('Contacts', 'fas fa-bars')->setSubItems([
            MenuItem::linkToCrud('Clients', 'fas fa-user', Client::class),
            MenuItem::linkToCrud('Fournisseurs', 'fas fa-truck', Fournisseur::class),
        ]);

        // Section Produits & Stocks
        yield MenuItem::subMenu('Gestion Produits', 'fas fa-bars')->setSubItems([
            MenuItem::linkToCrud('Produits', 'fas fa-box', Produit::class),
            //MenuItem::linkToCrud('Stocks', 'fas fa-warehouse', Stock::class), // si tu as une entité Stock
        ]);
        // Section Commandes
        yield MenuItem::subMenu('Commandes', 'fas fa-bars')->setSubItems([
            MenuItem::linkToCrud('Commandes d\'Achat', 'fas fa-shopping-cart', CommandeAchat::class),
            MenuItem::linkToCrud('Commandes de Vente', 'fas fa-cash-register', CommandeVente::class),
        ]);
        // Section Paiements
        yield MenuItem::subMenu('Paiements', 'fas fa-bars')->setSubItems([
            MenuItem::linkToCrud('Paiements', 'fas fa-credit-card', Paiement::class),
        ]);
        // Section Statistiques / Rapports
        yield MenuItem::subMenu('Rapports', 'fas fa-bars')->setSubItems([
            MenuItem::linkToRoute('Dashboard Statistiques', 'fas fa-chart-line', 'app_statistiques'), // route personnalisée
        ]);
        // Section Utilisateurs (si applicable)
        yield MenuItem::subMenu('Administration', 'fas fa-bars')->setSubItems([
            MenuItem::linkToCrud('Users', 'fa fa-users', User::class)
                ->setController(UserCrudController::class),
            MenuItem::linkToRoute('Paramètres', 'fas fa-cogs', 'app_settings'),
        ]);
        // Lien vers site public
        yield MenuItem::linkToUrl('Voir le site', 'fas fa-home', '/')->setLinkTarget('_blank');
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
