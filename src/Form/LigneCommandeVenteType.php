<?php

namespace App\Form;

use App\Entity\LigneCommandeVente;
use App\Entity\Produit;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormError;

class LigneCommandeVenteType extends AbstractType
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('produit', EntityType::class, [
                'class' => Produit::class,
                'choice_label' => function (Produit $produit) {
                    return $produit->getNom() . ' (' . $produit->getQuantiteStock() . ' en stock)';
                },
                'placeholder' => 'Choisir un produit',
                'choice_attr' => function (Produit $produit) {
                    return [
                        'data-prix' => $produit->getPrixVente(),
                        'disabled' => $produit->getQuantiteStock() <= 0 ? 'disabled' : false,
                    ];
                },
            ])
            ->add('quantite', IntegerType::class, [
                'label' => 'Quantité',
                'attr' => [
                    'min' => 1,
                    'class' => 'form-control'
                ]
            ])
            ->add('prixUnitaire', MoneyType::class, [
                'currency' => 'MAD',
                'required' => false,
            ]);

        // Mise à jour du prix unitaire lors du choix du produit
        $builder->get('produit')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $produit = $form->getData();

            if ($produit) {
                $form->getParent()->get('prixUnitaire')->setData($produit->getPrixVente());
            }
        });

        // Validation de la quantité par rapport au stock disponible
        $builder->get('quantite')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $quantite = $form->getData();
            $produit = $form->getParent()->get('produit')->getData();

            if ($produit && $quantite > $produit->getQuantiteStock()) {
                $form->getParent()->get('quantite')->addError(new FormError('La quantité demandée dépasse le stock disponible.'));
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => LigneCommandeVente::class,
        ]);
    }
}
