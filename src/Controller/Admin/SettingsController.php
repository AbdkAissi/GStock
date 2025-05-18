<?php
// src/Controller/Admin/SettingsController.php
namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class SettingsController extends AbstractController
{
    #[Route('/admin/settings', name: 'app_settings')]
    public function index(Request $request): Response
    {
        // Ici, tu pourrais récupérer les paramètres depuis la base ou config
        $currentLogoFilename = null; // ex: récupérer depuis DB

        $form = $this->createFormBuilder()
            ->add('siteName', TextType::class, [
                'label' => 'Nom du site',
                'required' => true,
                'data' => 'Mon super site', // valeur par défaut ou issue de la base
            ])
            ->add('logo', FileType::class, [
                'label' => 'Logo (image)',
                'mapped' => false, // non lié à une propriété d'entité
                'required' => false,
                'constraints' => [
                    new Image([
                        'maxSize' => '2M',
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (jpg, png, gif).',
                    ])
                ],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $siteName = $form->get('siteName')->getData();
            $logoFile = $form->get('logo')->getData();

            if ($logoFile) {
                $originalFilename = pathinfo($logoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $logoFile->guessExtension();

                try {
                    $logoFile->move(
                        $this->getParameter('logos_directory'),
                        $newFilename
                    );
                    $currentLogoFilename = $newFilename;
                    // Ici, tu peux sauvegarder $siteName et $newFilename en base ou fichier
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload du logo.');
                }
            }

            $this->addFlash('success', 'Paramètres enregistrés avec succès.');

            // Redirection pour éviter resoumission du formulaire
            return $this->redirectToRoute('app_settings');
        }

        return $this->render('admin/settings.html.twig', [
            'form' => $form->createView(),
            'logoFilename' => $currentLogoFilename,
        ]);
    }
}
