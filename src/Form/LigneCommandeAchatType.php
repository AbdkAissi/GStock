<?php
// src/Form/LigneCommandeAchatType.php
namespace App\Form;

use App\Entity\LigneCommandeAchat;
use App\Entity\Produit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;

class LigneCommandeAchatType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('produit', EntityType::class, [
                'class' => Produit::class,
                'choice_label' => 'nom',
                'label' => 'Produit',
                'attr' => [
                    'class' => 'form-control select2' // Optionnel pour un meilleur rendu
                ],
                'placeholder' => 'Sélectionnez un produit', // Optionnel
                'required' => true,
                'choice_attr' => function (Produit $produit) {
                    return ['data-prix-achat' => $produit->getPrixAchat()];
                }
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
                'attr' => ['class' => 'form-control'],
                //  'data' => 0, // Valeur par défaut
                'required' => true
            ]);
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => LigneCommandeAchat::class,
            'allow_extra_fields' => true, // Important pour EasyAdmin
            'csrf_protection' => false // Généralement désactivé dans EasyAdmin
        ]);
    }
    // LigneCommandeAchat.php
    /**
     * @ManyToOne(targetEntity="App\Entity\CommandeAchat", inversedBy="lignesCommandeAchat")
     * @JoinColumn(nullable=false)
     */
    private $commandeAchat;
}
