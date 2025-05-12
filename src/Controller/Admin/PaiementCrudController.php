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
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use App\Entity\Client;
use App\Entity\Fournisseur;


class PaiementCrudController extends AbstractCrudController
{
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
                ->setFormTypeOption('row_attr', ['data-field' => 'type-paiement']),


            ChoiceField::new('moyenPaiement', 'Méthode')->setChoices([
                'Espèces' => 'especes',
                'Carte bancaire' => 'carte_bancaire',
                'Chèque' => 'cheque',
                'Virement bancaire' => 'virement_bancaire',
            ])->allowMultipleChoices(false)
                ->renderExpanded(false),

            MoneyField::new('montant', 'Montant')
                ->setCurrency('MAD')
                ->setStoredAsCents(false),
        ];

        // Ajouts pour formulaire de création/édition
        if ($pageName === Crud::PAGE_NEW || $pageName === Crud::PAGE_EDIT) {
            $fields = array_merge($fields, [
                AssociationField::new('client')
                    ->hideOnIndex()
                    ->setFormTypeOption(
                        'row_attr',
                        ['data-role' => 'client-row']
                    ),

                AssociationField::new('fournisseur')
                    ->hideOnIndex()
                    ->setFormTypeOption(
                        'row_attr',
                        ['data-role' => 'fournisseur-row']
                    ),

                AssociationField::new('commandeVente')
                    ->setLabel('Commande de vente')
                    ->onlyOnForms(),

                AssociationField::new('commandeAchat')
                    ->setLabel('Commande d\'achat')
                    ->onlyOnForms(),

                TextEditorField::new('commentaire'),
            ]);
        }

        // Pour la vue index
        if ($pageName === Crud::PAGE_INDEX) {
            $fields[] = AssociationField::new('client')
                ->setLabel('Nom')
                ->formatValue(function ($value, $entity) {
                    return $entity->getBeneficiaire(); // méthode personnalisée pour afficher nom
                });

            $fields[] = TextField::new('commandeAssociee', 'Commande associée')
                ->formatValue(function ($value, $entity) {
                    if ($entity->getCommandeVente()) {
                        return 'Vente #' . $entity->getCommandeVente()->getId();
                    } elseif ($entity->getCommandeAchat()) {
                        return 'Achat #' . $entity->getCommandeAchat()->getId();
                    }
                    return 'Aucune';
                });
        }

        // Pour la vue détail
        if ($pageName === Crud::PAGE_DETAIL) {
            $fields[] = TextField::new('resumePaiements')
                ->setLabel('Historique')
                ->setTemplatePath('admin/paiement/_resume.html.twig')
                ->onlyOnDetail();
        }

        return $fields;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    // Méthode pour persister un paiement et vérifier la commande associée
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        parent::persistEntity($entityManager, $entityInstance);

        // Vérification de la commande après persistance du paiement
        $this->verifierCommandePayee($entityInstance, $entityManager);
    }

    // Méthode pour mettre à jour un paiement et vérifier la commande associée
    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        parent::updateEntity($entityManager, $entityInstance);

        // Vérification de la commande après mise à jour du paiement
        $this->verifierCommandePayee($entityInstance, $entityManager);
    }

    // Vérifie si la commande est payée
    private function verifierCommandePayee(Paiement $paiement, EntityManagerInterface $entityManager): void
    {
        $commande = null;

        // Vérifie si le paiement est associé à une commande d'achat ou une commande de vente
        if ($paiement->getCommandeAchat()) {
            $commande = $paiement->getCommandeAchat();
        } elseif ($paiement->getCommandeVente()) {
            $commande = $paiement->getCommandeVente();
        }

        if ($commande) {
            // Calcul du montant total de la commande
            $montantTotalCommande = $commande->getTotalCommande(); // Assurez-vous que la méthode getTotal() existe
            $montantPaye = array_sum(array_map(fn($p) => $p->getMontant(), $commande->getPaiements()->toArray()));

            // Si le montant payé est égal ou supérieur au montant total de la commande, on marque la commande comme payée
            if ($montantPaye >= $montantTotalCommande) {
                $commande->setEtat('payée');
                $entityManager->persist($commande);
                $entityManager->flush();
            }
        }
    }

    public function configureAssets(Assets $assets): Assets
    {
        return parent::configureAssets($assets)
            ->addJsFile('build/paiement.js');
    }
}
