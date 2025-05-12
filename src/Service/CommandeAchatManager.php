<?php

namespace App\Service;

use App\Entity\CommandeAchat;
use App\Service\StockManager;
use Doctrine\ORM\EntityManagerInterface;

class CommandeAchatManager
{
    private StockManager $stockManager;
    private EntityManagerInterface $entityManager;

    public function __construct(StockManager $stockManager, EntityManagerInterface $entityManager)
    {
        $this->stockManager = $stockManager;
        $this->entityManager = $entityManager;
    }

    /**
     * Ajoute les produits au stock suite à une commande d'achat.
     */
    public function validerCommande(CommandeAchat $commande): void
    {
        if ($commande->getEtat() !== 'receptionnee') {
            $this->stockManager->ajusterCommandeAchat($commande);
            $commande->setEtat('receptionnee');
            $this->entityManager->flush();
        }
    }


    /**
     * Enlève du stock les produits qui avaient été ajoutés par cette commande d'achat.
     */
    public function restaurerStock(CommandeAchat $commande): void
    {
        $this->stockManager->restaurerCommandeAchat($commande);
    }
}
