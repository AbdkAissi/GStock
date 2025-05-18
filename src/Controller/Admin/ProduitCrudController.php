<?php

namespace App\Controller\Admin;

use App\Entity\Produit;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class ProduitCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Produit::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('nom', 'Nom du produit'),
            TextareaField::new('description', 'Description')
                ->hideOnIndex(),
            MoneyField::new('prixAchat', 'Prix achat')
                ->setCurrency('MAD'),
            MoneyField::new('prixVente', 'Prix vente')
                ->setCurrency('MAD'),
            IntegerField::new('quantiteStock', 'Stock')
                ->formatValue(function ($value, $entity) {
                    $seuil = $entity->getSeuilAlerte();
                    $color = $value < $seuil ? 'red' : 'green';
                    return sprintf('<span style="color:%s; font-weight:bold;">%d</span>', $color, $value);
                })
                ->setTemplatePath('admin/fields/html_field.html.twig'),
            IntegerField::new('seuilAlerte', 'Seuil d\'alerte')->setSortable(false),
        ];
    }
}
