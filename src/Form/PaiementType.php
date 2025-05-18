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
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Client;
use App\Entity\Fournisseur;
use App\Entity\CommandeAchat;
use App\Entity\CommandeVente;

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

            ->add('typeDestinataire', ChoiceType::class, [
                'mapped' => false,
                'required' => true,
                'choices' => [
                    'Client' => 'client',
                    'Fournisseur' => 'fournisseur',
                ],
                'label' => 'Type de destinataire',
            ])

            // Champs client et fournisseur en EntityType, non obligatoires, visibles
            ->add('client', EntityType::class, [
                'class' => Client::class,
                'choice_label' => 'nom',
                'placeholder' => '-- Choisir client --',
                'required' => false,
                'label' => 'Client',
                'mapped' => true,
            ])
            ->add('fournisseur', EntityType::class, [
                'class' => Fournisseur::class,
                'choice_label' => 'nom',
                'placeholder' => '-- Choisir fournisseur --',
                'required' => false,
                'label' => 'Fournisseur',
                'mapped' => true,
            ])

            // Champs commande Achat / Vente liés
            ->add('commandeAchat', EntityType::class, [
                'class' => CommandeAchat::class,
                'choice_label' => 'id', // ou un champ label plus parlant
                'placeholder' => '-- Sélectionnez commande achat --',
                'required' => false,
                'label' => 'Commande Achat',
                'mapped' => true,
            ])
            ->add('commandeVente', EntityType::class, [
                'class' => CommandeVente::class,
                'choice_label' => 'id', // ou un champ label plus parlant
                'placeholder' => '-- Sélectionnez commande vente --',
                'required' => false,
                'label' => 'Commande Vente',
                'mapped' => true,
            ])

            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer',
            ]);
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Paiement::class,
        ]);
    }
}
