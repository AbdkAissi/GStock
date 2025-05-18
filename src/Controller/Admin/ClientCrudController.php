<?php
// src/Controller/Admin/ClientCrudController.php

namespace App\Controller\Admin;

use App\Entity\Client;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use Symfony\Component\HttpFoundation\Response;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class ClientCrudController extends AbstractCrudController
{

    public static function getEntityFqcn(): string
    {
        return Client::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Client')
            ->setEntityLabelInPlural('Clients')
            ->setPageTitle(Crud::PAGE_INDEX, 'Liste des clients');
    }

    public function configureActions(Actions $actions): Actions
    {
        $sonner = Action::new('sonnerClient', '📞 Sonner', 'fa fa-bell')
            ->linkToCrudAction('sonnerClient')
            ->setCssClass('btn btn-warning');

        return $actions
            // ->add(Crud::PAGE_DETAIL, $sonner)
            // ->add(Crud::PAGE_INDEX, $sonner) // 👈 ajoute ce bouton aussi à la liste
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }


    public function sonnerClient(AdminContext $context): Response
    {
        /** @var Client $client */
        $client = $context->getEntity()->getInstance();

        $this->addFlash('success', 'Client sonné : ' . $client->getNom());

        /** @var AdminUrlGenerator $adminUrlGenerator */
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);

        $url = $adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::INDEX)
            ->generateUrl();

        return $this->redirect($url);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),

            TextField::new('nom', 'Nom'),
            EmailField::new('email', 'Email')->setSortable(false),

            // ➕ Ajout du champ téléphone cliquable
            TextField::new('contact', 'Téléphone')
                ->setSortable(false)
                ->formatValue(fn($value, $entity) => "<a href='tel:$value'>$value</a>")
                ->renderAsHtml(),

            // ... ajoute d'autres champs si nécessaire
        ];
    }
}
