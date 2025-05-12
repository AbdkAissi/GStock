<?php
// src/Form/PaiementType.php

namespace App\Form;

use App\Entity\Paiement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class PaiementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('montant', MoneyType::class, [
                'currency' => 'MAD',
                'label' => 'Montant',
            ])
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date du paiement',
            ])
            ->add('moyenPaiement', ChoiceType::class, [
                'choices' => [
                    'Carte bancaire' => 'carte_bancaire',
                    'Espèces' => 'especes',
                    'Chèque' => 'cheque',
                    'Virement bancaire' => 'virement_bancaire',
                ],
                'label' => 'Méthode de paiement',
            ])
            ->add('commentaire', TextareaType::class, [
                'required' => false,
                'label' => 'Commentaire',
            ])
            ->add('client', HiddenType::class, [
                'required' => false,
                'mapped' => true,
            ])
            ->add('fournisseur', HiddenType::class, [
                'required' => false,
                'mapped' => true,
            ])

            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer',
            ])
            ->add('typeDestinataire', ChoiceType::class, [
                'mapped' => false, // ✅ important
                'required' => true,
                'choices' => [
                    'Client' => 'client',
                    'Fournisseur' => 'fournisseur',
                ],
                'label' => 'Type de destinataire',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Paiement::class,
        ]);
    }
}
