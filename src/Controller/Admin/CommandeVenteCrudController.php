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
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;

class CommandeVenteCrudController extends AbstractCrudController
{
    private EntityManagerInterface $entityManager;
    private UrlGeneratorInterface $symfonyUrlGenerator;
    private AdminUrlGenerator $adminUrlGenerator;
    private CommandeVenteManager $commandeVenteManager;
    private StockManager $stockManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        StockManager $stockManager,
        UrlGeneratorInterface $symfonyUrlGenerator,
        AdminUrlGenerator $adminUrlGenerator,
        CommandeVenteManager $commandeVenteManager
    ) {
        $this->entityManager = $entityManager;
        $this->stockManager = $stockManager;
        $this->symfonyUrlGenerator = $symfonyUrlGenerator;
        $this->adminUrlGenerator = $adminUrlGenerator;
        $this->commandeVenteManager = $commandeVenteManager;
    }

    public static function getEntityFqcn(): string
    {
        return CommandeVente::class;
    }

    public function configureAssets(Assets $assets): Assets
    {
        // Solution 1: Ne rien ajouter si vous voulez supprimer des assets
        return $assets;

        // OU Solution 2: Ajouter uniquement les assets nécessaires
        // return $assets
        //     ->addWebpackEncoreEntry('app') // Seulement les assets vraiment nécessaires
        //     ->addJsFile('js/paiement.js');
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
            ->displayIf(fn($entity) => $entity->getEtat() === 'en_attente'); // Ne s'affiche que pour les commandes en attente

        $annuler = Action::new('annulerCommande', 'Annuler', 'fa fa-times')
            ->linkToCrudAction('annulerCommande')
            ->setCssClass('btn btn-danger')
            ->displayIf(fn($entity) => $entity->getEtat() !== 'annulee'); // Ne s'affiche pas pour les commandes déjà annulées

        $imprimer = Action::new('imprimer', 'Imprimer', 'fa fa-print')
            ->linkToUrl(
                fn(CommandeVente $entity) =>
                $this->symfonyUrlGenerator->generate('commande_vente_imprimer', ['id' => $entity->getId()])
            )
            ->setCssClass('btn btn-secondary')
            ->setHtmlAttributes(['target' => '_blank']);

        return $actions
            ->add(Crud::PAGE_DETAIL, $valider)
            ->add(Crud::PAGE_DETAIL, $annuler)
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

    public function annulerCommande(AdminContext $context): RedirectResponse
    {
        $commande = $context->getEntity()->getInstance();
        dump("État avant annulation :", $commande->getEtat());

        if (!$commande instanceof CommandeVente) {
            $this->addFlash('danger', 'Commande invalide.');
            return $this->redirectToReferrer($context);
        }

        if ($commande->getEtat() === 'annulee') {
            $this->addFlash('warning', 'La commande est déjà annulée.');
            return $this->redirectToReferrer($context);
        }

        // Si la commande était déjà validée, restaurer le stock
        if ($commande->getEtat() === 'receptionnee') {
            $this->commandeVenteManager->restaurerStock($commande);
        }

        $commande->setEtat('annulee');
        $this->entityManager->flush();

        $this->addFlash('success', 'Commande annulée avec succès. Le stock a été mis à jour si nécessaire.');
        return $this->redirectToReferrer($context);
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

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof CommandeVente) {
            return;
        }

        // Récupération de l'état original avant modification
        $uow = $entityManager->getUnitOfWork();
        $uow->computeChangeSets();
        $originalData = $uow->getOriginalEntityData($entityInstance);

        // Récupération de la commande originale depuis la base
        $commandeOriginal = $entityManager->getRepository(CommandeVente::class)
            ->find($entityInstance->getId());

        $etaitValidee = $commandeOriginal->isValidee();
        $estValidee = $entityInstance->isValidee();

        // Gestion spécifique pour les commandes réceptionnées modifiées
        if ($etaitValidee) {
            $this->gererModificationStock($commandeOriginal, $entityInstance);
        }

        // Appliquer les modifications
        parent::updateEntity($entityManager, $entityInstance);

        // Gestion des nouvelles validations
        if (!$etaitValidee && $estValidee) {
            $this->stockManager->ajusterCommandeVente($entityInstance);
        }
    }
    private function gererModificationStock(CommandeVente $ancienneCommande, CommandeVente $nouvelleCommande): void
    {
        $entityManager = $this->entityManager;

        // Détection des changements
        $uow = $entityManager->getUnitOfWork();
        $uow->computeChangeSets();

        // Pour chaque ligne de la nouvelle commande
        foreach ($nouvelleCommande->getLignesCommandeVente() as $ligne) {
            $changeSet = $uow->getEntityChangeSet($ligne);

            // Si la quantité a été modifiée
            if (isset($changeSet['quantite'])) {
                $ancienneQuantite = $changeSet['quantite'][0];
                $nouvelleQuantite = $changeSet['quantite'][1];
                $difference = $nouvelleQuantite - $ancienneQuantite;

                if ($difference !== 0) {
                    try {
                        $this->stockManager->ajusterStock(
                            $ligne->getProduit(),
                            abs($difference),
                            'vente',
                            $difference < 0
                        );
                    } catch (\LogicException $e) {
                        // Gérer l'erreur de stock insuffisant
                        throw new \RuntimeException(
                            sprintf('Impossible de modifier la commande: %s', $e->getMessage())
                        );
                    }
                }
            }
        }

        // Gestion des lignes supprimées
        $originalLignes = $ancienneCommande->getLignesCommandeVente()->toArray();
        $nouvellesLignes = $nouvelleCommande->getLignesCommandeVente()->toArray();

        $lignesSupprimees = array_udiff(
            $originalLignes,
            $nouvellesLignes,
            function ($a, $b) {
                return $a->getId() - $b->getId();
            }
        );

        foreach ($lignesSupprimees as $ligne) {
            $this->stockManager->restaurerStock(
                $ligne->getProduit(),
                $ligne->getQuantite(),
                'vente'
            );
        }
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
        if ($commande->getEtat() === 'annulee') {
            $this->addFlash('danger', 'Une commande annulée ne peut pas être validée.');
            return $this->redirectToReferrer($context);
        }

        $etatAvantValidation = $commande->getEtat();

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

        if ($etatAvantValidation === 'annulee') {
            $this->addFlash('info', 'Commande annulée revalidée avec succès et stock mis à jour.');
        } else {
            $this->addFlash('success', 'Commande validée et stock mis à jour.');
        }

        $url = $this->adminUrlGenerator
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
                ->setController(self::class)
                ->setAction('index')
                ->generateUrl();
        }

        return $this->redirect($referrer);
    }
}
