<?php

namespace App\Entity;

use App\Repository\LigneCommandeAchatRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\CommandeAchat;
use App\Entity\Produit;

#[ORM\Entity(repositoryClass: LigneCommandeAchatRepository::class)]
class LigneCommandeAchat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $quantite = null;

    #[ORM\Column]
    private ?float $prixUnitaire = null;

    #[ORM\ManyToOne(
        targetEntity: CommandeAchat::class,
        inversedBy: 'lignesCommandeAchat',
        cascade: ['persist']
    )]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?CommandeAchat $commandeAchat = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Produit $produit = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): static
    {
        if ($quantite <= 0) {
            throw new \InvalidArgumentException("La quantité doit être positive.");
        }
        $this->quantite = $quantite;
        return $this;
    }

    public function getPrixUnitaire(): ?float
    {
        return $this->prixUnitaire;
    }

    public function setPrixUnitaire(float $prixUnitaire): static
    {
        if ($prixUnitaire <= 0) {
            throw new \InvalidArgumentException("Le prix unitaire doit être positif.");
        }
        $this->prixUnitaire = $prixUnitaire;
        return $this;
    }

    public function getCommandeAchat(): ?CommandeAchat
    {
        return $this->commandeAchat;
    }

    public function setCommandeAchat(?CommandeAchat $commandeAchat): static
    {
        $this->commandeAchat = $commandeAchat;
        return $this;
    }

    public function getProduit(): ?Produit
    {
        return $this->produit;
    }

    public function setProduit(?Produit $produit): static
    {
        $this->produit = $produit;
        return $this;
    }
    public function getTotal(): float
    {
        return $this->quantite * $this->prixUnitaire;
    }
}
