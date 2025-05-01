<?php

namespace App\Controller\Admin;

use App\Entity\Client;
use App\Entity\CommandeAchat;
use App\Entity\CommandeVente;
use App\Entity\Fournisseur;
use App\Entity\Produit;
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

class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private ProduitRepository $produitRepository,
        private ClientRepository $clientRepository,
        private CommandeVenteRepository $commandeVenteRepository,
        private CommandeAchatRepository $commandeAchatRepository
    ) {}

    #[Route('/admin', name: 'app_dashboard')]
    public function index(): Response
    {
        $produitsCount = $this->produitRepository->count([]);
        $clientsCount = $this->clientRepository->count([]);
        $commandesvCount = $this->commandeVenteRepository->count([]);
        $commandesaCount = $this->commandeAchatRepository->count([]);

        return $this->render('admin/empty_dashboard.html.twig', [
            'totalProduits' => $produitsCount,
            'totalClients' => $clientsCount,
            'totalCommandesVente' => $commandesvCount,
            'totalCommandesAchat' => $commandesaCount
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('<i class="fas fa-warehouse"></i> Gestion de Stock')
            ->setFaviconPath('favicon.ico');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::section('Gestion Commerciale');
        yield MenuItem::linkToCrud('Produits', 'fas fa-box', Produit::class);
        yield MenuItem::linkToCrud('Clients', 'fas fa-users', Client::class);

        yield MenuItem::section('Gestion Logistique');
        yield MenuItem::linkToCrud('Fournisseurs', 'fas fa-truck', Fournisseur::class);
        yield MenuItem::linkToCrud('Commandes Achat', 'fas fa-shopping-cart', CommandeAchat::class);
        yield MenuItem::linkToCrud('Commandes Vente', 'fas fa-cash-register', CommandeVente::class);
    }

    public function configureAssets(): Assets
    {
        return parent::configureAssets()
            ->addWebpackEncoreEntry('admin')
            ->addJsFile('js/auto-prix-vente.js')
            ->addJsFile('js/auto-prix-achat.js')
            ->addCssFile('css/admin.css');
    }
}
