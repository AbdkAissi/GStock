<?php

namespace App\Service;

use App\Entity\CommandeVente;
use App\Entity\Produit;
use Doctrine\ORM\EntityManagerInterface;

class CommandeVenteManager
{
    private StockManager $stockManager;
    private EntityManagerInterface $entityManager;

    public function __construct(StockManager $stockManager, EntityManagerInterface $entityManager)
    {
        $this->stockManager = $stockManager;
        $this->entityManager = $entityManager;
    }

    /**
     * Ajuste le stock en fonction de la validation d'une commande vente.
     */
    public function validerCommande(CommandeVente $commande): void
    {
        foreach ($commande->getLignesCommandeVente() as $ligne) {
            $produit = $ligne->getProduit();
            $quantiteDemandee = $ligne->getQuantite();

            if ($produit->getQuantiteStock() < $quantiteDemandee) {
                throw new \Exception(sprintf(
                    'Stock insuffisant pour "%s" : Stock actuel %d, requis %d',
                    $produit->getNom(),
                    $produit->getQuantiteStock(),
                    $quantiteDemandee
                ));
            }

            $this->stockManager->ajusterStock($produit, $quantiteDemandee, 'vente');
        }
    }

    /**
     * Restaure le stock si la commande est annulée après avoir été réceptionnée.
     */
    public function restaurerStock(CommandeVente $commande): void
    {
        foreach ($commande->getLignesCommandeVente() as $ligne) {
            $produit = $ligne->getProduit();
            $quantite = $ligne->getQuantite();

            $this->stockManager->ajusterStock($produit, $quantite, 'retour');
        }
    }
}
