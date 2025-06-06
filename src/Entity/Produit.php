<?php

namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    #[Assert\GreaterThanOrEqual(0)]
    private ?int $quantiteStock = null;

    #[ORM\Column]
    private ?float $prixAchat = null;

    #[ORM\Column]
    private ?float $prixVente = null;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    #[Assert\NotBlank(message: 'Le code-barres ne peut pas être vide.')]
    #[Assert\Regex(
        pattern: '/^prod_[a-f0-9\-]{36}$/',
        message: 'Le code-barres doit commencer par "prod_" suivi d’un UUID valide.'
    )]
    private ?string $barcode = null;

    public function getBarcode(): ?string
    {
        return $this->barcode;
    }

    public function setBarcode(string $barcode): self
    {
        $this->barcode = $barcode;
        return $this;
    }

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToMany(mappedBy: 'produit', targetEntity: StockHistorique::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $historiques;

    #[ORM\Column]
    #[Assert\Range(min: 0)]
    private ?int $seuilAlerte = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->historiques = new ArrayCollection();
    }

    public function getHistoriques(): Collection
    {
        return $this->historiques;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getQuantiteStock(): ?int
    {
        return $this->quantiteStock;
    }

    public function setQuantiteStock(int $quantiteStock): static
    {
        $this->quantiteStock = $quantiteStock;
        return $this;
    }

    public function getPrixAchat(): ?float
    {
        return $this->prixAchat;
    }

    public function setPrixAchat(float $prixAchat): static
    {
        $this->prixAchat = $prixAchat;
        return $this;
    }

    public function getPrixVente(): ?float
    {
        return $this->prixVente;
    }

    public function setPrixVente(float $prixVente): static
    {
        $this->prixVente = $prixVente;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getSeuilAlerte(): ?int
    {
        return $this->seuilAlerte;
    }

    public function setSeuilAlerte(int $seuilAlerte): static
    {
        $this->seuilAlerte = $seuilAlerte;
        return $this;
    }

    // --- Méthode magique pour la conversion en chaîne ---
    public function __toString(): string
    {
        return $this->nom ?? 'Produit sans nom';
    }
}
