<?php

namespace App\Entity;

use App\Repository\StockHistoriqueRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: StockHistoriqueRepository::class)]
class StockHistorique
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Produit::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Produit $produit = null;

    #[ORM\Column(type: 'integer')]
    #[Assert\Range(min: 1)]
    private int $quantite;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $date;

    #[ORM\Column(type: 'string', length: 50)]
    private string $operationType; // Remplacé 'type' par 'operationType'

    #[ORM\Column(type: 'text', nullable: true)]  // 'text' pour plus de flexibilité
    private ?string $commentaire = null;

    // --- Constructeur ---
    public function __construct()
    {
        $this->date = new \DateTimeImmutable();
    }

    // --- Getters & Setters ---
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduit(): ?Produit
    {
        return $this->produit;
    }

    public function setProduit(?Produit $produit): self
    {
        $this->produit = $produit;
        return $this;
    }

    public function getQuantite(): int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): self
    {
        $this->quantite = $quantite;
        return $this;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): self
    {
        $this->date = $date;
        return $this;
    }

    public function getOperationType(): string
    {
        return $this->operationType;
    }

    public function setOperationType(string $operationType): self
    {
        $this->operationType = $operationType;
        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): self
    {
        $this->commentaire = $commentaire;
        return $this;
    }

    // --- Méthodes supplémentaires ---
    public function ajouterQuantite(int $quantite): void
    {
        $this->quantite += $quantite;
    }

    public function retirerQuantite(int $quantite): void
    {
        $this->quantite -= $quantite;
    }
}
