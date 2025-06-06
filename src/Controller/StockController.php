<?php

namespace App\Controller;

use App\Entity\Produit;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\StockHistorique;

class StockController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/stock/interface', name: 'stock_interface', methods: ['GET'])]
    public function interface(): Response
    {
        return $this->render('stock/index.html.twig');
    }

    #[Route('/stock/scan', name: 'stock_scan', methods: ['POST'])]
    public function scanProduct(Request $request): JsonResponse
    {
        $barcode = $request->get('barcode');

        $product = $this->entityManager->getRepository(Produit::class)->findOneBy(['barcode' => $barcode]);

        if (!$product) {
            return new JsonResponse(['status' => 'error', 'message' => 'Produit non trouvé'], 404);
        }

        return new JsonResponse([
            'status' => 'success',
            'produit' => [
                'name' => $product->getNom(),
                'quantite' => $product->getQuantiteStock(),
                'prix' => $product->getPrixVente(),
            ]
        ]);
    }

    #[Route('/stock/adjust/{action}', name: 'stock_adjust', methods: ['POST'])]

    public function adjustStock(Request $request, string $action): JsonResponse
    {
        $barcode = $request->get('barcode');
        $quantite = (int) $request->get('quantite');

        $produit = $this->entityManager->getRepository(Produit::class)->findOneBy(['barcode' => $barcode]);

        if (!$produit) {
            return new JsonResponse(['status' => 'error', 'message' => 'Produit non trouvé'], 404);
        }

        if ($quantite <= 0) {
            return new JsonResponse(['status' => 'error', 'message' => 'La quantité doit être un nombre positif.'], 400);
        }

        if ($action === 'add') {
            $produit->setQuantiteStock($produit->getQuantiteStock() + $quantite);
        } elseif ($action === 'subtract') {
            if ($produit->getQuantiteStock() < $quantite) {
                return new JsonResponse(['status' => 'error', 'message' => 'Quantité insuffisante pour retirer.'], 400);
            }
            $produit->setQuantiteStock($produit->getQuantiteStock() - $quantite);
        }

        // Créer l'historique du stock
        $historique = new StockHistorique();
        $historique->setProduit($produit);
        $historique->setQuantite($quantite);
        $historique->setDate(new \DateTimeImmutable());
        $historique->setOperationType($action); // 'add' ou 'subtract'
        $historique->setCommentaire("Ajustement de stock via l'interface");

        $this->entityManager->persist($historique);
        $this->entityManager->flush();

        return new JsonResponse(['status' => 'success', 'new_quantity' => $produit->getQuantiteStock()]);
    }
}
