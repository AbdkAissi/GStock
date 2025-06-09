<?php

namespace App\Controller\Admin;

use App\Entity\Paiement;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class PaiementCrudController extends AbstractCrudController
{
    public function __construct(private AdminUrlGenerator $adminUrlGenerator) {}

    public static function getEntityFqcn(): string
    {
        return Paiement::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Paiement')
            ->setEntityLabelInPlural('Paiements')
            ->setPageTitle(Crud::PAGE_INDEX, 'Liste des paiements')
            ->setPageTitle(Crud::PAGE_NEW, 'Ajouter un paiement')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier le paiement')
            ->setPageTitle(Crud::PAGE_DETAIL, fn(Paiement $paiement) => 'Détails du paiement #' . $paiement->getId())
            ->setDefaultSort(['date' => 'DESC'])
            ->setFormThemes([
                '@EasyAdmin/crud/form_theme.html.twig',
            ]);
    }

    public function configureFields(string $pageName): iterable
    {
        $fields = [
            IdField::new('id')->hideOnForm(),
            DateField::new('date', 'Date')->setFormat('d/M/Y'),

            ChoiceField::new('typePaiement', 'Type')
                ->setChoices([
                    'Client' => 'client',
                    'Fournisseur' => 'fournisseur',
                ])
                ->setFormTypeOption('attr', ['id' => 'type-paiement'])
                ->setFormTypeOption('row_attr', ['data-field' => 'type-paiement'])
                ->formatValue(function ($value) {
                    return [
                        'client' => 'Client',
                        'fournisseur' => 'Fournisseur'
                    ][$value] ?? $value;
                }),

            ChoiceField::new('moyenPaiement', 'Méthode')->setChoices([
                'Espèces' => 'especes',
                'Carte bancaire' => 'carte_bancaire',
                'Chèque' => 'cheque',
                'Virement bancaire' => 'virement_bancaire',
            ])
                ->allowMultipleChoices(false)
                ->renderExpanded(false)
                ->formatValue(function ($value) {
                    return [
                        'especes' => 'Espèces',
                        'carte_bancaire' => 'Carte bancaire',
                        'cheque' => 'Chèque',
                        'virement_bancaire' => 'Virement bancaire'
                    ][$value] ?? $value;
                }),

            MoneyField::new('montant', 'Montant')
                ->setCurrency('MAD')
                ->setStoredAsCents(false),
        ];

        if ($pageName === Crud::PAGE_INDEX) {
            // SOLUTION 1: Version sans HTML pour éviter les problèmes de rendu
            $fields[] = TextField::new('commandeAssociee', 'Commande associée')
                ->onlyOnIndex()
                ->setSortable(false);
            // EasyAdmin utilisera automatiquement getCommandeAssociee() de l'entité
            // Résultat : "Vente #53", "Achat #31", "Aucune"

            $fields[] = TextField::new('nomBeneficiaire', 'Nom')
                ->onlyOnIndex()
                ->setSortable(false);
            // EasyAdmin utilisera automatiquement getNomBeneficiaire() de l'entité
        }

        if ($pageName === Crud::PAGE_DETAIL) {
            // Version avec HTML seulement pour la page de détail
            $fields[] = TextField::new('commandeAssociee', 'Commande associée')
                ->onlyOnDetail()
                ->renderAsHtml()
                ->formatValue(function ($value, $entity) {
                    if ($entity->getCommandeVente()) {
                        $url = $this->adminUrlGenerator
                            ->setController(CommandeVenteCrudController::class)
                            ->setAction('detail')
                            ->setEntityId($entity->getCommandeVente()->getId())
                            ->generateUrl();
                        return sprintf('<a href="%s" target="_blank" style="color:#0d6efd;">Commande Vente #%d</a>', $url, $entity->getCommandeVente()->getId());
                    } elseif ($entity->getCommandeAchat()) {
                        $url = $this->adminUrlGenerator
                            ->setController(CommandeAchatCrudController::class)
                            ->setAction('detail')
                            ->setEntityId($entity->getCommandeAchat()->getId())
                            ->generateUrl();
                        return sprintf('<a href="%s" target="_blank" style="color:#0d6efd;">Commande Achat #%d</a>', $url, $entity->getCommandeAchat()->getId());
                    }
                    return 'Aucune';
                })
                ->setSortable(false);

            $fields[] = TextField::new('resumePaiements')
                ->setLabel('Historique')
                ->setTemplatePath('admin/paiement/_resume.html.twig')
                ->onlyOnDetail();
        }

        // Champs pour création / édition
        if ($pageName === Crud::PAGE_NEW || $pageName === Crud::PAGE_EDIT) {
            $fields = array_merge($fields, [
                AssociationField::new('client')
                    ->hideOnIndex()
                    ->setFormTypeOption('row_attr', ['data-role' => 'client-row']),

                AssociationField::new('fournisseur')
                    ->hideOnIndex()
                    ->setFormTypeOption('row_attr', ['data-role' => 'fournisseur-row']),

                AssociationField::new('commandeVente')
                    ->setLabel('Commande de vente')
                    ->onlyOnForms()
                    ->setFormTypeOption('row_attr', ['data-role' => 'commande-vente-row']),

                AssociationField::new('commandeAchat')
                    ->setLabel('Commande d\'achat')
                    ->onlyOnForms()
                    ->setFormTypeOption('row_attr', ['data-role' => 'commande-achat-row']),

                TextEditorField::new('commentaire'),
            ]);
        }

        return $fields;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        parent::persistEntity($entityManager, $entityInstance);
        $this->verifierCommandePayee($entityInstance, $entityManager);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        parent::updateEntity($entityManager, $entityInstance);
        $this->verifierCommandePayee($entityInstance, $entityManager);
    }

    private function verifierCommandePayee(Paiement $paiement, EntityManagerInterface $entityManager): void
    {
        $commande = null;

        if ($paiement->getCommandeAchat()) {
            $commande = $paiement->getCommandeAchat();
        } elseif ($paiement->getCommandeVente()) {
            $commande = $paiement->getCommandeVente();
        }

        if ($commande) {
            $montantTotalCommande = $commande->getTotalCommande();
            $montantPaye = array_sum(array_map(fn($p) => $p->getMontant(), $commande->getPaiements()->toArray()));

            if ($montantPaye >= $montantTotalCommande) {
                $commande->setEtat('payée');
                $entityManager->persist($commande);
                $entityManager->flush();
            }
        }
    }

    public function configureAssets(Assets $assets): Assets
    {
        // TEMPORAIREMENT: désactiver le JS pour tester
        return parent::configureAssets($assets);
        // ->addJsFile('build/paiement.js');
    }
}
