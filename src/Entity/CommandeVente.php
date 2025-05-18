<?php
// src/Entity/CommandeVente.php

namespace App\Entity;

use App\Repository\CommandeVenteRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\LigneCommandeVente;
use App\Entity\Client;
use App\Entity\Paiement;

#[ORM\Entity(repositoryClass: CommandeVenteRepository::class)]
class CommandeVente
{
    public const ETAT_EN_ATTENTE = 'en_attente';
    public const ETAT_ANNULEE = 'annulee';
    public const ETAT_RECEPTIONNEE = 'receptionnee';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateCommande = null;

    #[ORM\Column(type: 'string', length: 20)]
    private string $etat = self::ETAT_EN_ATTENTE;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Client $client = null;

    #[ORM\OneToMany(mappedBy: 'commandeVente', targetEntity: LigneCommandeVente::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $lignesCommandeVente;

    #[ORM\OneToMany(mappedBy: 'commandeVente', targetEntity: Paiement::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $paiements;

    public function __construct()
    {
        $this->lignesCommandeVente = new ArrayCollection();
        $this->paiements = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setLignesCommandeVente(Collection $lignes): static
    {
        // Marquer les lignes qui ne sont plus présentes pour suppression
        foreach ($this->lignesCommandeVente->toArray() as $existingLigne) {
            $found = false;
            foreach ($lignes as $newLigne) {
                if ($newLigne->getId() && $existingLigne->getId() === $newLigne->getId()) {
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $this->removeLigneCommandeVente($existingLigne);
            }
        }

        // Ajouter/Mettre à jour les nouvelles lignes
        foreach ($lignes as $ligne) {
            // Si c'est une nouvelle ligne ou une ligne non associée à cette commande
            if (!$ligne->getCommandeVente() || $ligne->getCommandeVente()->getId() !== $this->getId()) {
                $this->addLigneCommandeVente($ligne);
            }
        }

        return $this;
    }
    public function getLignesCommandeAffichage(): string
    {
        if ($this->getLignesCommandeVente()->isEmpty()) {
            return '<div class="text-muted">Aucune ligne</div>';
        }

        $id = 'accordion-lignes-' . $this->getId();
        $html = <<<HTML
<div class="accordion" id="{$id}">
  <div class="accordion-item bg-light text-dark">
    <h2 class="accordion-header" id="heading{$id}">
      <button class="accordion-button collapsed py-1 px-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{$id}" aria-expanded="false" aria-controls="collapse{$id}">
        Voir les lignes de commande
      </button>
    </h2>
    <div id="collapse{$id}" class="accordion-collapse collapse" aria-labelledby="heading{$id}" data-bs-parent="#{$id}">
      <div class="accordion-body p-2">
        <ul class="mb-0 ps-3">
HTML;

        foreach ($this->getLignesCommandeVente() as $ligne) {
            $produit = htmlspecialchars($ligne->getProduit()->getNom());
            $quantite = $ligne->getQuantite();
            $prixUnitaire = number_format($ligne->getPrixUnitaire(), 2, ',', ' ');
            $total = number_format($ligne->getQuantite() * $ligne->getPrixUnitaire(), 2, ',', ' ');
            $html .= "<li><strong>{$produit}</strong> × {$quantite} à {$prixUnitaire} MAD (Total : {$total} MAD)</li>";
        }

        $html .= '</ul></div></div></div></div>';

        return $html;
    }

    public function getDateCommande(): ?\DateTimeImmutable
    {
        return $this->dateCommande;
    }

    public function setDateCommande(\DateTimeImmutable $dateCommande): static
    {
        $this->dateCommande = $dateCommande;
        return $this;
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(string $etat): static
    {
        $this->etat = $etat;
        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }
    public function isValidee(): bool
    {
        return $this->etat === self::ETAT_RECEPTIONNEE;
    }

    public function isAnnulee(): bool
    {
        return $this->etat === self::ETAT_ANNULEE;
    }

    public function isEnAttente(): bool
    {
        return $this->etat === self::ETAT_EN_ATTENTE;
    }

    public static function getEtatsDisponibles(): array
    {
        return [
            'En attente' => self::ETAT_EN_ATTENTE,
            'Réceptionnée' => self::ETAT_RECEPTIONNEE,
            'Annulée' => self::ETAT_ANNULEE,
        ];
    }


    public function setClient(?Client $client): static
    {
        $this->client = $client;
        return $this;
    }

    public function getLignesCommandeVente(): Collection
    {
        return $this->lignesCommandeVente;
    }

    public function addLigneCommandeVente(LigneCommandeVente $ligne): static
    {
        if (!$this->lignesCommandeVente->contains($ligne)) {
            $this->lignesCommandeVente[] = $ligne;
            $ligne->setCommandeVente($this);
        }
        return $this;
    }

    public function removeLigneCommandeVente(LigneCommandeVente $ligne): static
    {
        if ($this->lignesCommandeVente->removeElement($ligne)) {
            if ($ligne->getCommandeVente() === $this) {
                $ligne->setCommandeVente(null);
            }
        }
        return $this;
    }

    public function getTotalCommande(): float
    {
        $total = 0;
        foreach ($this->lignesCommandeVente as $ligne) {
            $prixUnitaire = $ligne->getPrixUnitaire(); // ne doit jamais être null, ou 0 par défaut
            $quantite = $ligne->getQuantite(); // ne doit jamais être null, ou 0 par défaut
            if ($prixUnitaire > 0 && $quantite > 0) {
                $total += $prixUnitaire * $quantite;
            }
        }
        return $total;
    }
    public function getMontantTotalPaye(): float
    {
        $total = 0;
        foreach ($this->getPaiements() as $paiement) {
            $total += $paiement->getMontant();
        }
        return $total;
    }

    public function getResteAPayer(): float
    {
        return $this->getTotalCommande() - $this->getMontantTotalPaye();
    }

    public function getPaiements(): Collection
    {
        return $this->paiements;
    }
    public function getEtatPaiement(): string
    {
        $total = $this->getTotalCommande() ?? 0;
        $paye = $this->getMontantTotalPaye() ?? 0;

        if ($paye >= $total) {
            return 'payée';
        } elseif ($paye > 0) {
            return 'partielle';
        } else {
            return 'impayée';
        }
    }

    public function __toString(): string
    {
        return 'Vente #' . $this->getId(); // ou n'importe quel champ lisible
    }
}
