<?php

namespace App\Controller\Admin;

use App\Entity\Produit;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

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
            TextareaField::new('description', 'Description')->hideOnIndex(),
            MoneyField::new('prixAchat', 'Prix achat')->setCurrency('MAD'),
            MoneyField::new('prixVente', 'Prix vente')->setCurrency('MAD'),
            TextField::new('barcode')
                ->setRequired(false) // Laisser facultatif, on génère si vide
                ->setHelp('Laisser vide pour générer automatiquement')
                ->setFormTypeOption('empty_data', function () {
                    return uniqid('prod_');
                }),
            IntegerField::new('quantiteStock', 'Stock')
                ->formatValue(function ($value, $entity) {
                    $seuil = $entity->getSeuilAlerte();
                    $color = ($value < $seuil) ? 'red' : 'green';
                    return sprintf('<span style="color:%s; font-weight:bold;">%d</span>', $color, $value);
                })
                ->setTemplatePath('admin/fields/html_field.html.twig'),
            IntegerField::new('seuilAlerte', 'Seuil d\'alerte')->setSortable(false),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Produit && empty($entityInstance->getBarcode())) {
            $entityInstance->setBarcode(uniqid('prod_'));
        }

        parent::persistEntity($entityManager, $entityInstance);
    }
}
