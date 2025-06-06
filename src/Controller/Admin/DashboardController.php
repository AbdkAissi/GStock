<?php

namespace App\Controller\Admin;

use App\Entity\Client;
use App\Entity\Fournisseur;
use App\Entity\Produit;
use App\Entity\CommandeAchat;
use App\Entity\CommandeVente;
use App\Entity\Paiement;
use App\Entity\User;

use App\Repository\ProduitRepository;
use App\Repository\ClientRepository;
use App\Repository\FournisseurRepository;
use App\Repository\CommandeAchatRepository;
use App\Repository\CommandeVenteRepository;
use App\Repository\PaiementRepository;

use App\Controller\Admin\ClientCrudController;
use App\Controller\Admin\FournisseurCrudController;
use App\Controller\Admin\ProduitCrudController;
use App\Controller\Admin\CommandeAchatCrudController;
use App\Controller\Admin\CommandeVenteCrudController;
use App\Controller\Admin\UserCrudController;

use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private ProduitRepository $produitRepository,
        private ClientRepository $clientRepository,
        private FournisseurRepository $fournisseurRepository,
        private CommandeAchatRepository $commandeAchatRepository,
        private CommandeVenteRepository $commandeVenteRepository,
        private PaiementRepository $paiementRepository,
        private AdminUrlGenerator $adminUrlGenerator,
    ) {}

    #[Route('/admin', name: 'app_dashboard')]
    public function index(): Response
    {
        $anneeActuelle = (int) date('Y');

        return $this->render('admin/empty_dashboard.html.twig', [
            // Données globales
            'totalProduits' => $this->produitRepository->count([]),
            'totalClients' => $this->clientRepository->count([]),
            'totalFournisseurs' => $this->fournisseurRepository->count([]),
            'totalCommandesVente' => $this->commandeVenteRepository->count([]),
            'totalCommandesAchat' => $this->commandeAchatRepository->count([]),

            // Graphiques
            'ventesParMois' => $this->commandeVenteRepository->getVentesParMois(),
            'achatsParMois' => $this->commandeAchatRepository->getAchatsParMois(),

            // Paiements
            'paiementsParEtat' => $this->paiementRepository->getMontantsParEtatParAnnee($anneeActuelle),
            'anneeActuelle' => $anneeActuelle,
            'anneesPaiements' => $this->paiementRepository->getAnneesPaiements(),

            // Produits critiques
            'produitsStockFaible' => $this->produitRepository->createQueryBuilder('p')
                ->where('p.quantiteStock <= p.seuilAlerte')
                ->getQuery()
                ->getResult(),


            // Dernières ventes
            'dernieresCommandesVente' => $this->commandeVenteRepository->createQueryBuilder('c')
                ->orderBy('c.dateCommande', 'DESC')
                ->setMaxResults(5)
                ->getQuery()
                ->getResult(),

            // Liens vers les entités
            'urls' => [
                'produits' => $this->adminUrlGenerator->setController(ProduitCrudController::class)->generateUrl(),
                'clients' => $this->adminUrlGenerator->setController(ClientCrudController::class)->generateUrl(),
                'fournisseurs' => $this->adminUrlGenerator->setController(FournisseurCrudController::class)->generateUrl(),
                'commandes_vente' => $this->adminUrlGenerator->setController(CommandeVenteCrudController::class)->generateUrl(),
                'commandes_achat' => $this->adminUrlGenerator->setController(CommandeAchatCrudController::class)->generateUrl(),
            ],
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
        yield MenuItem::section('Données');

        yield MenuItem::subMenu('Contacts', 'fas fa-address-book')->setSubItems([
            MenuItem::linkToCrud('Clients', 'fas fa-user', Client::class),
            MenuItem::linkToCrud('Fournisseurs', 'fas fa-truck', Fournisseur::class),
        ]);

        yield MenuItem::subMenu('Produits', 'fas fa-boxes')->setSubItems([
            MenuItem::linkToCrud('Produits', 'fas fa-box', Produit::class),
        ]);

        yield MenuItem::section('Commandes & Paiements');

        yield MenuItem::subMenu('Commandes', 'fas fa-shopping-basket')->setSubItems([
            MenuItem::linkToCrud('Achats', 'fas fa-shopping-cart', CommandeAchat::class),
            MenuItem::linkToCrud('Ventes', 'fas fa-cash-register', CommandeVente::class),
        ]);

        yield MenuItem::subMenu('Paiements', 'fas fa-credit-card')->setSubItems([
            MenuItem::linkToCrud('Paiements', 'fas fa-credit-card', Paiement::class),
        ]);

        yield MenuItem::subMenu('Rapports', 'fas fa-chart-pie')->setSubItems([
            MenuItem::linkToRoute('Statistiques', 'fas fa-chart-line', 'app_dashboard'),
        ]);

        yield MenuItem::section('Système');

        yield MenuItem::subMenu('Administration', 'fas fa-user-cog')->setSubItems([
            MenuItem::linkToCrud('Utilisateurs', 'fas fa-users', User::class)->setController(UserCrudController::class),
            MenuItem::linkToRoute('Paramètres', 'fas fa-cogs', 'app_settings'),
        ]);

        yield MenuItem::linkToUrl('Voir le site', 'fas fa-home', '/')->setLinkTarget('_blank');
    }

    public function configureAssets(): Assets
    {
        return parent::configureAssets()
            ->addWebpackEncoreEntry('admin')
            ->addJsFile('build/auto-prix-vente.js')
            ->addJsFile('build/auto-prix-achat.js')
            ->addCssFile('build/admin.css');
    }
}
