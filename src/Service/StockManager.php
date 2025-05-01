<?php

namespace App\Service;

use App\Entity\Produit;
use Doctrine\ORM\EntityManagerInterface;

class StockManager
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function ajusterStock(Produit $produit, int $quantite, string $type): void
    {
        if ($type === 'vente') {
            $produit->setQuantiteStock($produit->getQuantiteStock() - $quantite);
        } elseif ($type === 'achat') {
            $produit->setQuantiteStock($produit->getQuantiteStock() + $quantite);
        } elseif ($type === 'retour') {
            $produit->setQuantiteStock($produit->getQuantiteStock() + $quantite);
        }

        $this->entityManager->persist($produit);
    }
}
