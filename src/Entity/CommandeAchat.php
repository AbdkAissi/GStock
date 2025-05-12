<?php
// src/Entity/CommandeAchat.php

namespace App\Entity;

use App\Repository\CommandeAchatRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\LigneCommandeAchat;
use App\Entity\Fournisseur;
use App\Entity\Paiement;

#[ORM\Entity(repositoryClass: CommandeAchatRepository::class)]
class CommandeAchat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateCommande = null;

    #[ORM\Column(length: 255)]
    private ?string $etat = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Fournisseur $fournisseur = null;

    #[ORM\OneToMany(targetEntity: Paiement::class, mappedBy: "commandeAchat")]
    private Collection $paiements;

    #[ORM\OneToMany(mappedBy: 'commandeAchat', targetEntity: LigneCommandeAchat::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $lignesCommandeAchat;

    public function __construct()
    {
        $this->paiements = new ArrayCollection();
        $this->lignesCommandeAchat = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getFournisseur(): ?Fournisseur
    {
        return $this->fournisseur;
    }

    public function setFournisseur(?Fournisseur $fournisseur): static
    {
        $this->fournisseur = $fournisseur;
        return $this;
    }

    public function getLignesCommandeAchat(): Collection
    {
        return $this->lignesCommandeAchat;
    }

    public function setLignesCommandeAchat(Collection $lignes): static
    {
        foreach ($this->lignesCommandeAchat as $existingLigne) {
            if (!$lignes->contains($existingLigne)) {
                $this->removeLigneCommandeAchat($existingLigne);
            }
        }

        foreach ($lignes as $ligne) {
            if (!$this->lignesCommandeAchat->contains($ligne)) {
                $this->addLigneCommandeAchat($ligne);
            }
        }

        return $this;
    }

    public function addLigneCommandeAchat(LigneCommandeAchat $ligne): static
    {
        if (!$this->lignesCommandeAchat->contains($ligne)) {
            $this->lignesCommandeAchat[] = $ligne;
            $ligne->setCommandeAchat($this);
        }
        return $this;
    }

    public function removeLigneCommandeAchat(LigneCommandeAchat $ligne): static
    {
        if ($this->lignesCommandeAchat->removeElement($ligne)) {
            if ($ligne->getCommandeAchat() === $this) {
                $ligne->setCommandeAchat(null);
            }
        }
        return $this;
    }

    public function getTotalCommande(): float
    {
        $total = 0;
        foreach ($this->lignesCommandeAchat as $ligne) {
            $prixUnitaire = $ligne->getPrixUnitaire();
            $quantite = $ligne->getQuantite();
            if ($prixUnitaire > 0 && $quantite > 0) {
                $total += $prixUnitaire * $quantite;
            }
        }
        return $total;
    }

    public function getLignesCommandeAffichage(): string
    {
        if ($this->getLignesCommandeAchat()->isEmpty()) {
            return '<em>Aucune ligne</em>';
        }

        $html = '<details><summary>Voir détails</summary><ul style="margin:0;padding-left:15px">';
        foreach ($this->getLignesCommandeAchat() as $ligne) {
            $produit = htmlspecialchars($ligne->getProduit()->getNom());
            $quantite = $ligne->getQuantite();
            $prixUnitaire = number_format($ligne->getPrixUnitaire(), 2, ',', ' ');
            $totalLigne = number_format($ligne->getQuantite() * $ligne->getPrixUnitaire(), 2, ',', ' ');

            $html .= sprintf(
                '<li>%s × %d à %s MAD (Total : %s MAD)</li>',
                $produit,
                $quantite,
                $prixUnitaire,
                $totalLigne
            );
        }
        $html .= '</ul></details>';

        return $html;
    }

    public function getPaiements(): Collection
    {
        return $this->paiements;
    }
    public function __toString(): string
    {
        return 'Achat #' . $this->getId(); // ou un champ pertinent comme fournisseur ou date
    }
}
