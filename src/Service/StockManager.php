<?php

namespace App\Service;

use App\Entity\Produit;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\CommandeVente;
use Psr\Log\LoggerInterface;
use App\Entity\CommandeAchat;
use App\Entity\StockHistorique;

class StockManager
{
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $logger = null // Le null permet de rendre le paramètre optionnel
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }
    private function log(string $message, array $context = []): void
    {
        if ($this->logger) {
            $this->logger->info($message, $context);
        }
    }
    /**
     * Ajuste le stock en fonction du type d'opération.
     */
    public function ajusterStock(Produit $produit, int $quantite, string $type, bool $restauration = false): void
    {
        $stockActuel = $produit->getQuantiteStock();

        // Logique de modification
        if ($type === 'vente') {
            $modification = $restauration ? $quantite : -$quantite;
        } elseif ($type === 'achat' || $type === 'retour') {
            $modification = $restauration ? -$quantite : $quantite;
        } else {
            throw new \InvalidArgumentException("Type d'opération non reconnu: $type");
        }

        $nouveauStock = $stockActuel + $modification;

        // Validation du stock
        if ($nouveauStock < 0) {
            throw new \LogicException(
                sprintf(
                    'Stock insuffisant pour "%s". Stock actuel: %d, tentative de modification: %d',
                    $produit->getNom(),
                    $stockActuel,
                    $modification
                )
            );
        }

        // Application de la modification
        $produit->setQuantiteStock($nouveauStock);
        $this->entityManager->persist($produit);

        // Journalisation (optionnel)
        $this->logger->info(sprintf(
            'Stock %s pour "%s": %d → %d (%s)',
            $restauration ? 'restauré' : 'ajusté',
            $produit->getNom(),
            $stockActuel,
            $nouveauStock,
            $type
        ), [
            'produit_id' => $produit->getId(),
            'ancien_stock' => $stockActuel,
            'nouveau_stock' => $nouveauStock,
            'operation' => $type
        ]);
    }

    /**
     * Restaure le stock (inverse de l’ajustement) selon le type.
     */
    public function restaurerStock(Produit $produit, int $quantite, string $type): void
    {
        $this->modifierStock($produit, $quantite, $type, true);
    }

    /**
     * Modifie le stock en fonction du type d'opération et si c’est une restauration.
     */
    private function modifierStock(Produit $produit, int $quantite, string $type, bool $restauration = false): void
    {
        dump("Modification stock : produit={$produit->getNom()}, qte=$quantite, type=$type, restauration=" . ($restauration ? 'oui' : 'non'));

        $stockActuel = $produit->getQuantiteStock();
        $nouveauStock = $stockActuel;

        // Détermine l'effet de l'opération sur le stock
        if ($type === 'vente') {
            $nouveauStock += $restauration ? $quantite : -$quantite;
        } elseif ($type === 'achat' || $type === 'retour') {
            $nouveauStock += $restauration ? -$quantite : $quantite;
        } else {
            throw new \InvalidArgumentException("Type d'opération non reconnu: $type");
        }

        // Sécurité : le stock ne peut pas devenir négatif
        if ($nouveauStock < 0) {
            throw new \LogicException("Le stock du produit ne peut pas être négatif.");
        }

        // Applique la nouvelle quantité
        $produit->setQuantiteStock($nouveauStock);

        // Enregistre la modification immédiatement
        $this->entityManager->persist($produit);
        $this->entityManager->flush();
    }
    public function restaurerCommandeVente(CommandeVente $commande): void
    {
        foreach ($commande->getLignesCommandeVente() as $ligne) {
            $produit = $ligne->getProduit();
            $quantite = $ligne->getQuantite();
            dump("RESTAURATION STOCK - Produit: " . $produit->getNom() . ", +" . $quantite); // Debug

            $this->restaurerStock($produit, $quantite, 'vente');
        }
    }
    public function ajusterCommandeVente(CommandeVente $commande): void
    {
        foreach ($commande->getLignesCommandeVente() as $ligne) {
            $this->ajusterStock($ligne->getProduit(), $ligne->getQuantite(), 'vente');
        }
    }

    public function ajusterCommandeAchat(CommandeAchat $commande): void
    {
        foreach ($commande->getLignesCommandeAchat() as $ligne) {
            $produit = $ligne->getProduit();
            $quantite = $ligne->getQuantite();

            $this->ajusterStock($produit, $quantite, 'achat');

            $historique = new StockHistorique();
            $historique->setProduit($produit);
            $historique->setQuantite($quantite);
            $historique->setDate(new \DateTimeImmutable());
            $historique->setOperationType('achat'); // ✅ le champ correct est operationType
            $historique->setCommentaire('Commande achat #' . $commande->getId());

            $this->entityManager->persist($historique);
        }

        $this->entityManager->flush();
    }

    public function restaurerCommandeAchat(CommandeAchat $commande): void
    {
        foreach ($commande->getLignesCommandeAchat() as $ligne) {
            $produit = $ligne->getProduit();
            $quantite = $ligne->getQuantite();

            $this->restaurerStock($produit, $quantite, 'achat');

            $historique = new StockHistorique();
            $historique->setProduit($produit);
            $historique->setQuantite(-$quantite); // Retrait de stock
            $historique->setDate(new \DateTimeImmutable());
            $historique->setOperationType('annulation'); // ✅ utilise le bon nom de champ
            $historique->setCommentaire('Annulation commande achat #' . $commande->getId());

            $this->entityManager->persist($historique);
        }

        $this->entityManager->flush();
    }
}
