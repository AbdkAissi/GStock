<?php

namespace App\Controller;

use App\Entity\ResetPasswordRequest;
use App\Form\ResetPasswordRequestFormType;
use App\Form\ChangePasswordFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class ResetPasswordController extends AbstractController
{
    #[Route('/reset/password', name: 'app_reset_password')]
    public function requestReset(Request $request, EntityManagerInterface $em, MailerInterface $mailer): Response
    {
        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $token = Uuid::v4();
            $resetRequest = new ResetPasswordRequest();
            $resetRequest->setEmail($email);
            $resetRequest->setToken($token);
            $resetRequest->setExpiresAt(new \DateTime('+1 hour'));

            $em->persist($resetRequest);
            $em->flush();

            // Générer le lien
            $resetUrl = $this->generateUrl('app_reset_password_token', [
                'token' => $token
            ], UrlGeneratorInterface::ABSOLUTE_URL);

            $emailMessage = (new Email())
                ->from('no-reply@tondomaine.com')
                ->to($email)
                ->subject('Réinitialisation de votre mot de passe')
                ->html("<p>Pour réinitialiser votre mot de passe, cliquez sur le lien suivant :</p><p><a href=\"$resetUrl\">Réinitialiser le mot de passe</a></p>");

            $mailer->send($emailMessage);


            $this->addFlash('success', 'Si cet email existe, un lien de réinitialisation a été envoyé.');
            return $this->redirectToRoute('app_reset_password');
        }

        return $this->render('reset_password/index.html.twig', [
            'requestForm' => $form->createView(),
        ]);
    }

    #[Route('/reset/password/{token}', name: 'app_reset_password_token')]
    public function resetWithToken(
        string $token,
        Request $request,
        EntityManagerInterface $em,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $resetRequest = $em->getRepository(ResetPasswordRequest::class)->findOneBy(['token' => $token]);

        if (!$resetRequest || $resetRequest->getExpiresAt() < new \DateTime()) {
            $this->addFlash('error', 'Ce lien de réinitialisation est invalide ou expiré.');
            return $this->redirectToRoute('app_reset_password');
        }

        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $userRepository->findOneBy(['email' => $resetRequest->getEmail()]);

            if (!$user) {
                $this->addFlash('error', 'Aucun compte trouvé pour cet email.');
                return $this->redirectToRoute('app_reset_password');
            }

            $hashedPassword = $passwordHasher->hashPassword($user, $form->get('plainPassword')->getData());
            $user->setPassword($hashedPassword);

            $em->remove($resetRequest);
            $em->flush();

            $this->addFlash('success', 'Votre mot de passe a été mis à jour avec succès.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('reset_password/change_password.html.twig', [
            'passwordForm' => $form->createView(),
        ]);
    }
}
