<?php

namespace App\Controller\Admin;

use App\Entity\CommandeVente;
use App\Form\LigneCommandeVenteType;
use App\Service\StockManager;
use App\Service\CommandeVenteManager;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class CommandeVenteCrudController extends AbstractCrudController
{
    private EntityManagerInterface $entityManager;
    private UrlGeneratorInterface $urlGenerator;
    private CommandeVenteManager $commandeVenteManager;
    private StockManager $stockManager;
    public function __construct(

        EntityManagerInterface $entityManager,
        StockManager $stockManager,
        UrlGeneratorInterface $urlGenerator,
        CommandeVenteManager $commandeVenteManager
    ) {
        $this->stockManager = $stockManager;
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->commandeVenteManager = $commandeVenteManager;
        //dd($this->stockManager); // Ajoute ça pour tester
    }

    public static function getEntityFqcn(): string
    {
        return CommandeVente::class;
    }

    public function configureAssets(Assets $assets): Assets
    {
        return parent::configureAssets($assets)
            ->addJsFile('build/app.js');
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Commande vente')
            ->setEntityLabelInPlural('Commandes vente')
            ->setPageTitle(Crud::PAGE_INDEX, 'Liste des commandes vente')
            ->setPageTitle(Crud::PAGE_NEW, 'Créer une commande vente')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier une commande vente')
            ->setFormThemes([
                '@EasyAdmin/crud/form_theme.html.twig',
                'admin/commande_vente/edit.html.twig',
            ])
            ->setPageTitle(Crud::PAGE_DETAIL, fn($entity) => 'Détails de la commande #' . $entity->getId());
    }

    public function configureFields(string $pageName): iterable
    {
        return [
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
                    'réceptionnée' => 'success',
                    'annulee' => 'danger',
                ]),
            Field::new('totalCommande', 'Total de la commande')
                ->onlyOnDetail()
                ->formatValue(fn($value, $entity) => number_format($entity->getTotalCommande(), 2, ',', ' ') . ' MAD'),
            AssociationField::new('client')
                ->setFormTypeOption('choice_label', 'nom'),
            CollectionField::new('lignesCommandeVente', 'Lignes de commande')
                ->setEntryType(LigneCommandeVenteType::class)
                ->allowAdd()
                ->allowDelete()
                ->renderExpanded()
                ->setFormTypeOption('by_reference', false)
                ->setFormTypeOption('prototype_name', '__ligne_idx__')
                ->setTemplatePath('admin/fields/lignes_commande.html.twig')
                ->setEntryIsComplex(true),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $valider = Action::new('validerCommande', 'Valider', 'fa fa-check')
            ->linkToCrudAction('validerCommande')
            ->setCssClass('btn btn-success')
            ->displayIf(fn($entity) => in_array($entity->getEtat(), ['en_attente', 'annulee']));

        $imprimer = Action::new('imprimer', 'Imprimer', 'fa fa-print')
            ->linkToUrl(
                fn(CommandeVente $entity) =>
                $this->urlGenerator->generate('commande_vente_imprimer', ['id' => $entity->getId()])
            )
            ->setCssClass('btn btn-secondary')
            ->setHtmlAttributes(['target' => '_blank']);

        return $actions
            ->add(Crud::PAGE_DETAIL, $valider)
            ->add(Crud::PAGE_DETAIL, $imprimer)
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
        if (!$entityInstance instanceof CommandeVente) {
            return;
        }

        if ($entityInstance->getDateCommande() === null) {
            $entityInstance->setDateCommande(new \DateTimeImmutable());
        }

        if ($entityInstance->getEtat() === 'receptionnee') {
            $this->commandeVenteManager->validerCommande($entityInstance);
        }

        parent::persistEntity($em, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $em, $entityInstance): void
    {
        if (!$entityInstance instanceof CommandeVente) {
            return;
        }

        $ancienneCommande = $this->entityManager->getUnitOfWork()->getOriginalEntityData($entityInstance);
        $ancienEtat = $ancienneCommande['etat'] ?? null;
        $nouvelEtat = $entityInstance->getEtat();

        if ($ancienEtat === 'réceptionnée' && $nouvelEtat === 'annulee') {
            $this->commandeVenteManager->restaurerStock($entityInstance);
            $this->addFlash('info', 'Stock restauré pour la commande.');
        }

        parent::updateEntity($em, $entityInstance);
    }

    public function validerCommande(AdminContext $context): RedirectResponse
    {
        $commande = $context->getEntity()->getInstance();

        if (!$commande instanceof CommandeVente) {
            $this->addFlash('danger', 'Commande invalide.');
            return $this->redirectToReferrer($context);
        }

        if ($commande->getEtat() === 'receptionnee') {
            $this->addFlash('warning', 'La commande est déjà réceptionnée.');
            return $this->redirectToReferrer($context);
        }

        // 🟰 ON AJOUTE ICI
        $etatAvantValidation = $commande->getEtat();

        // Vérification du stock
        foreach ($commande->getLignesCommandeVente() as $ligne) {
            $produit = $ligne->getProduit();
            $quantiteDemandee = $ligne->getQuantite();

            if ($produit->getQuantiteStock() < $quantiteDemandee) {
                $this->addFlash('danger', sprintf(
                    'Stock insuffisant pour le produit "%s". Stock actuel : %d, requis : %d',
                    $produit->getNom(),
                    $produit->getQuantiteStock(),
                    $quantiteDemandee
                ));
                return $this->redirectToReferrer($context);
            }
        }

        $commande->setEtat('receptionnee');

        foreach ($commande->getLignesCommandeVente() as $ligne) {
            $this->stockManager->ajusterStock(
                $ligne->getProduit(),
                $ligne->getQuantite(),
                'vente'
            );
        }

        $this->entityManager->flush();

        // ✅ MESSAGE SPÉCIAL
        if ($etatAvantValidation === 'annulee') {
            $this->addFlash('info', 'Commande annulée revalidée avec succès et stock mis à jour.');
        } else {
            $this->addFlash('success', 'Commande validée et stock mis à jour.');
        }

        $url = $this->urlGenerator->generate('admin', [
            'crudAction' => 'detail',
            'crudControllerFqcn' => CommandeVenteCrudController::class,
            'entityId' => $commande->getId(),
        ]);

        return $this->redirect($url);
    }

    private function redirectToReferrer(AdminContext $context): RedirectResponse
    {
        $referrer = $context->getReferrer();

        if (!$referrer) {
            $referrer = $this->urlGenerator->generate('admin', [
                'crudAction' => 'index',
                'crudControllerFqcn' => self::class,
            ]);
        }

        return $this->redirect($referrer);
    }
}
