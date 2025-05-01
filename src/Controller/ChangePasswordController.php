<?php
// src/Controller/ChangePasswordController.php

namespace App\Controller;

use App\Form\ChangePasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Form\ChangePasswordFormType;

#[Route('/profil')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class ChangePasswordController extends AbstractController
{
    #[Route('/changer-mot-de-passe', name: 'change_password')]
    public function changePassword(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em
    ) {
        /** @var \App\Entity\User $user */

        $user = $this->getUser();

        $form = $this->createForm(ChangePasswordFormType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $oldPassword = $form->get('oldPassword')->getData();
            $newPassword = $form->get('newPassword')->getData();

            if (!$passwordHasher->isPasswordValid($user, $oldPassword)) {
                $this->addFlash('error', 'Ancien mot de passe incorrect.');
            } else {
                $user->setPassword(
                    $passwordHasher->hashPassword($user, $newPassword)
                );
                $em->flush();

                $this->addFlash('success', 'Mot de passe changé avec succès !');

                return $this->redirectToRoute('profile_edit');
            }
        }

        return $this->render('profile/change_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
