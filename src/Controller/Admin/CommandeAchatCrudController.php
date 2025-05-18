<?php

namespace App\Controller\Admin;

use App\Entity\CommandeAchat;
use App\Form\LigneCommandeAchatType;
use App\Service\StockManager;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;

class CommandeAchatCrudController extends AbstractCrudController
{
    private EntityManagerInterface $entityManager;
    private AdminUrlGenerator $adminUrlGenerator;
    private StockManager $stockManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        AdminUrlGenerator $adminUrlGenerator,
        StockManager $stockManager
    ) {
        $this->entityManager = $entityManager;
        $this->adminUrlGenerator = $adminUrlGenerator;
        $this->stockManager = $stockManager;
    }

    public static function getEntityFqcn(): string
    {
        return CommandeAchat::class;
    }

    public function configureAssets(Assets $assets): Assets
    {
        return parent::configureAssets($assets)
            ->addWebpackEncoreEntry('auto-prix-achat');
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Commande achat')
            ->setEntityLabelInPlural('Commandes achat')
            ->setPageTitle(Crud::PAGE_INDEX, 'Liste des commandes achat')
            ->setPageTitle(Crud::PAGE_NEW, 'Créer une commande achat')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier une commande achat')
            ->setFormThemes([
                '@EasyAdmin/crud/form_theme.html.twig',
                'admin/commande_achat/edit.html.twig',
            ])
            ->setPageTitle(Crud::PAGE_DETAIL, fn($entity) => 'Détails de la commande #' . $entity->getId());
    }

    public function configureFields(string $pageName): iterable
    {
        $fields = [
            IdField::new('id')->hideOnForm(),
            DateField::new('dateCommande', 'Date commande')->setFormat('dd/MM/yyyy HH:mm'),
            ChoiceField::new('etat', 'État')
                ->setChoices([
                    'En attente' => 'en_attente',
                    'Réceptionnée' => 'receptionnee',
                    'Annulée' => 'annulee',
                ])
                ->renderAsBadges([
                    'en_attente' => 'warning',
                    'receptionnee' => 'success',
                    'annulee' => 'danger',
                ]),
            ChoiceField::new('etatPaiement', 'Paiement')
                ->onlyOnIndex()
                ->formatValue(fn($value, $entity) => $entity->getEtatPaiement())
                ->renderAsBadges([
                    'payée' => 'success',
                    'partielle' => 'warning',
                    'impayée' => 'danger',
                ]),

            AssociationField::new('fournisseur')
                ->setFormTypeOption('choice_label', 'nom'),
            CollectionField::new('lignesCommandeAchat', 'Lignes de commande')
                ->setEntryType(LigneCommandeAchatType::class)
                ->allowAdd()
                ->allowDelete()
                ->renderExpanded()
                ->setFormTypeOption('by_reference', false)
                ->setFormTypeOption('prototype_name', '__ligne_idx__')
                ->setTemplatePath('admin/fields/lignes_commande.html.twig')
                ->setEntryIsComplex(true),
        ];

        if ($pageName === Crud::PAGE_DETAIL) {
            // ✅ Historique des paiements
            $fields[] = AssociationField::new('paiements', 'Historique des paiements')
                ->onlyOnDetail()
                ->setTemplatePath('admin/fields/historique_paiements.html.twig');
            $fields[] = MoneyField::new('totalCommande', 'Total de la commande')
                ->setCurrency('MAD')
                ->formatValue(fn($value, $entity) => number_format($entity->getTotalCommande(), 2, ',', ' ') . ' MAD');

            $fields[] = Field::new('montantTotalPaye', 'Montant payé')
                ->onlyOnDetail()
                ->setVirtual(true)
                ->formatValue(fn($value, $entity) => number_format($entity->getMontantTotalPaye(), 2, ',', ' ') . ' MAD');

            $fields[] = Field::new('resteAPayer', 'Reste à payer')
                ->onlyOnDetail()
                ->setVirtual(true)
                ->formatValue(fn($value, $entity) => number_format($entity->getResteAPayer(), 2, ',', ' ') . ' MAD');
        }

        return $fields;
    }


    public function configureActions(Actions $actions): Actions
    {
        $valider = Action::new('validerCommande', 'Valider', 'fa fa-check')
            ->linkToCrudAction('validerCommande')
            ->setCssClass('btn btn-success')
            ->displayIf(fn($entity) => in_array($entity->getEtat(), ['en_attente', 'annulee']));

        return $actions
            ->add(Crud::PAGE_DETAIL, $valider)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(
                Crud::PAGE_INDEX,
                Action::EDIT,
                fn(Action $action) =>
                $action->displayIf(fn($entity) => $entity->getEtat() !== 'annulee')
            )
            ->update(
                Crud::PAGE_DETAIL,
                Action::EDIT,
                fn(Action $action) =>
                $action->displayIf(fn($entity) => $entity->getEtat() !== 'annulee')
            );
    }

    public function persistEntity(EntityManagerInterface $em, $entityInstance): void
    {
        if (!$entityInstance instanceof CommandeAchat) {
            return;
        }

        if ($entityInstance->getDateCommande() === null) {
            $entityInstance->setDateCommande(new \DateTimeImmutable());
        }

        if ($entityInstance->getEtat() === 'receptionnee') {
            foreach ($entityInstance->getLignesCommandeAchat() as $ligne) {
                $this->stockManager->ajusterStock(
                    $ligne->getProduit(),
                    $ligne->getQuantite(),
                    'achat'
                );
            }
        }

        parent::persistEntity($em, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $em, $entityInstance): void
    {
        if (!$entityInstance instanceof CommandeAchat) {
            return;
        }

        $ancienneCommande = $this->entityManager->getUnitOfWork()->getOriginalEntityData($entityInstance);
        $ancienEtat = $ancienneCommande['etat'] ?? null;
        $nouvelEtat = $entityInstance->getEtat();

        if ($ancienEtat === 'receptionnee' && $nouvelEtat === 'annulee') {
            foreach ($entityInstance->getLignesCommandeAchat() as $ligne) {
                $this->stockManager->restaurerStock(
                    $ligne->getProduit(),
                    $ligne->getQuantite(),
                    'achat'
                );
            }
            $this->addFlash('info', 'Stock restauré pour la commande.');
        }

        parent::updateEntity($em, $entityInstance);
    }

    public function validerCommande(AdminContext $context): RedirectResponse
    {
        $commande = $context->getEntity()->getInstance();

        if (!$commande instanceof CommandeAchat) {
            $this->addFlash('danger', 'Commande invalide.');
            return $this->redirectToReferrer($context);
        }

        if ($commande->getEtat() === 'receptionnee') {
            $this->addFlash('warning', 'La commande est déjà réceptionnée.');
            return $this->redirectToReferrer($context);
        }

        $commande->setEtat('receptionnee');

        foreach ($commande->getLignesCommandeAchat() as $ligne) {
            $this->stockManager->ajusterStock(
                $ligne->getProduit(),
                $ligne->getQuantite(),
                'achat'
            );
        }

        $this->entityManager->flush();

        $this->addFlash('success', 'Commande validée et stock mis à jour.');

        $url = $this->adminUrlGenerator
            ->setDashboard(DashboardController::class)
            ->setController(self::class)
            ->setAction('detail')
            ->setEntityId($commande->getId())
            ->generateUrl();

        return $this->redirect($url);
    }

    private function redirectToReferrer(AdminContext $context): RedirectResponse
    {
        $referrer = $context->getReferrer();

        if (!$referrer) {
            $referrer = $this->adminUrlGenerator
                ->setDashboard(DashboardController::class)
                ->setController(self::class)
                ->setAction('index')
                ->generateUrl();
        }

        return $this->redirect($referrer);
    }
}
