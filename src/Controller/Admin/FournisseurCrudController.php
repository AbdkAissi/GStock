<?php

namespace App\Controller\Admin;

use App\Entity\Fournisseur;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action; // Action dans Config
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions; // Actions dans Config
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\TextFilterType;

class FournisseurCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Fournisseur::class;
    }

    /*  public function configureActions(Actions $actions): Actions
    {
        // Désactive l'action "delete"
        return $actions
            ->disable(Action::DELETE); // Désactivation de l'action delete
    } */


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),

            TextField::new('nom', 'Nom'),
            TextEditorField::new('adresse', 'Adresse')->setSortable(false),

            // ➕ Ajout du champ téléphone cliquable
            TextField::new('contact', 'Téléphone')->setSortable(false)
                ->formatValue(fn($value, $entity) => "<a href='tel:$value'>$value</a>")
                ->renderAsHtml(),

            // ... ajoute d'autres champs si nécessaire
        ];
    }
}
