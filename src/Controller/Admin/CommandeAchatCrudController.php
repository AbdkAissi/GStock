<?php

namespace App\Controller\Admin;

use App\Entity\CommandeAchat;
use App\Form\LigneCommandeAchatType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\StockManager;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use Symfony\Component\HttpFoundation\RedirectResponse;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;

class CommandeAchatCrudController extends AbstractCrudController
{
    private StockManager $stockManager;
    private EntityManagerInterface $entityManager;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(StockManager $stockManager, EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator)
    {
        $this->stockManager = $stockManager;
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
    }

    public static function getEntityFqcn(): string
    {
        return CommandeAchat::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $valider = Action::new('validerCommande', 'Valider', 'fa fa-check')
            ->linkToCrudAction('validerCommande')
            ->setCssClass('btn btn-success')
            ->displayIf(fn($entity) => $entity->getEtat() !== 'réceptionnée');

        $imprimer = Action::new('imprimer', 'Imprimer', 'fa fa-print')
            ->linkToUrl(function (CommandeAchat $entity) {
                return $this->urlGenerator->generate('commande_achat_imprimer', ['id' => $entity->getId()]);
            })
            ->setCssClass('btn btn-secondary')
            ->setHtmlAttributes(['target' => '_blank']);

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_DETAIL, $valider)
            ->add(Crud::PAGE_INDEX, $valider)
            ->add(Crud::PAGE_DETAIL, $imprimer);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Commande d\'achat')
            ->setEntityLabelInPlural('Commandes d\'achat')
            ->setPageTitle(Crud::PAGE_INDEX, 'Liste des commandes d\'achat')
            ->setPageTitle(Crud::PAGE_NEW, 'Créer une commande d\'achat')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier une commande d\'achat')
            ->setFormThemes([
                '@EasyAdmin/crud/form_theme.html.twig',
                'admin/commande_achat/edit.html.twig', // ← ton template personnalisé
            ])
            ->setPageTitle(Crud::PAGE_DETAIL, fn($entity) => 'Détails de la commande #' . $entity->getId());
    }

    public function validerCommande(AdminContext $context): RedirectResponse
    {
        // 🔵 Récupération de l'entité CommandeAchat depuis le contexte EasyAdmin
        $commande = $context->getEntity()->getInstance();

        // 🟡 Si la commande n'est pas encore réceptionnée
        if ($commande->getEtat() !== 'réceptionnée') {
            // ✅ On change son état en "réceptionnée"
            $commande->setEtat('réceptionnée');

            // 🔄 Pour chaque ligne de commande associée
            foreach ($commande->getLignesCommandeAchat() as $ligne) {
                // 🔵 Si la ligne n'a pas encore de lien avec la commande (sécurité supplémentaire)
                if ($ligne->getCommandeAchat() === null) {
                    // ➡️ On l'associe à la commande en cours
                    $ligne->setCommandeAchat($commande);
                }

                // ✅ On ajuste le stock pour le produit de la ligne (opération d'**achat**)
                $this->stockManager->ajusterStock(
                    $ligne->getProduit(),  // Le produit concerné
                    $ligne->getQuantite(), // Quantité achetée
                    'achat'                // Type d'opération : ici c'est un achat
                );
            }

            // 💾 On enregistre toutes les modifications dans la base de données
            $this->entityManager->flush();

            // 🎉 On affiche un message de succès
            $this->addFlash('success', 'Commande validée et stock mis à jour.');
        }

        // 🔵 Génération de l'URL pour rediriger vers la page de détails de la commande
        $url = $this->urlGenerator->generate('admin', [
            'crudAction' => 'detail',                     // Action EasyAdmin : afficher le détail
            'crudControllerFqcn' => get_class($this),      // Contrôleur actuel
            'entityId' => $commande->getId(),              // ID de la commande
        ]);

        // 🔙 Redirection vers la page de détail de la commande
        return $this->redirect($url);
    }


    public function persistEntity(EntityManagerInterface $em, $entityInstance): void
    {
        if (!$entityInstance instanceof CommandeAchat) {
            return;
        }

        foreach ($entityInstance->getLignesCommandeAchat() as $ligne) {
            if ($ligne->getCommandeAchat() === null) {
                $ligne->setCommandeAchat($entityInstance);
            }

            // Mise à jour du stock uniquement si l'état est déjà "réceptionnée"
            if ($entityInstance->getEtat() === 'réceptionnée') {
                $this->stockManager->ajusterStock($ligne->getProduit(), $ligne->getQuantite(), 'achat');
            }
        }

        parent::persistEntity($em, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $em, $entityInstance): void
    {
        if (!$entityInstance instanceof CommandeAchat) {
            return;
        }

        foreach ($entityInstance->getLignesCommandeAchat() as $ligne) {
            if ($ligne->getCommandeAchat() === null) {
                $ligne->setCommandeAchat($entityInstance);
            }

            // Mise à jour du stock uniquement si l'état est déjà "réceptionnée"
            if ($entityInstance->getEtat() === 'réceptionnée') {
                $this->stockManager->ajusterStock($ligne->getProduit(), $ligne->getQuantite(), 'achat');
            }
        }

        parent::updateEntity($em, $entityInstance);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            DateField::new('dateCommande')->setFormat('dd/MM/yyyy'),

            ChoiceField::new('etat', 'État')
                ->setChoices([
                    'En attente' => 'en_attente',
                    'Réceptionnée' => 'réceptionnée',
                    'Annulée' => 'annulee',
                ])
                ->renderAsBadges([
                    'en_attente' => 'warning',
                    'réceptionnée' => 'success',
                    'annulee' => 'danger',
                ]),

            Field::new('totalCommande', 'Total de la commande')
                ->onlyOnDetail()
                ->formatValue(function ($value, $entity) {
                    return number_format($entity->getTotalCommande(), 2, ',', ' ') . ' MAD';
                }),

            AssociationField::new('fournisseur')
                ->setFormTypeOption('choice_label', 'nom')
                ->setFormTypeOption('attr', ['class' => 'form-control']),

            CollectionField::new('lignesCommandeAchat', 'Lignes de commande')
                ->setEntryType(LigneCommandeAchatType::class)
                ->allowAdd()
                ->allowDelete()
                ->renderExpanded()
                ->setFormTypeOption('by_reference', false)
                ->setFormTypeOption('prototype_name', '__ligne_idx__')
                ->setEntryIsComplex(true)
                ->setTemplatePath('admin/fields/lignes_commande.html.twig')
                ->setFormTypeOption('mapped', true),
        ];
    }
}
