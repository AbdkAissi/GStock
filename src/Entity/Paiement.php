<?php

namespace App\Entity;

use App\Repository\PaiementRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: PaiementRepository::class)]
#[Assert\Callback('validateClientOrFournisseur')]
class Paiement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTime $date = null;

    #[ORM\Column]
    private ?float $montant = null;

    #[ORM\Column(length: 10)]
    private ?string $moyenPaiement = null;

    #[ORM\ManyToOne(inversedBy: 'paiements')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Client $client = null;

    #[ORM\ManyToOne(inversedBy: 'paiements')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Fournisseur $fournisseur = null;
    #[ORM\Column(length: 20, nullable: true)]
    private ?string $typePaiement = null;
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $commentaire = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): static
    {
        $this->date = $date;
        return $this;
    }

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(float $montant): static
    {
        $this->montant = $montant;
        return $this;
    }

    public function getMoyenPaiement(): ?string
    {
        return $this->moyenPaiement;
    }

    public function setMoyenPaiement(string $moyenPaiement): static
    {
        $this->moyenPaiement = $moyenPaiement;
        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): static
    {
        $this->client = $client;
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

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): static
    {
        $this->commentaire = $commentaire;
        return $this;
    }

    public function getNomBeneficiaire(): string
    {
        if ($this->client) {
            return $this->client->getNom();
        }

        if ($this->fournisseur) {
            return $this->fournisseur->getNom();
        }

        return 'N/A';
    }

    // src/Entity/Paiement.php

    public function getBeneficiaire(): ?string
    {
        return $this->getNomBeneficiaire(); // Utilise la méthode existante
    }

    public function getTotalPaiements(): float
    {
        $beneficiaire = $this->client ?? $this->fournisseur;
        if (!$beneficiaire) {
            return 0;
        }

        return array_reduce(
            $beneficiaire->getPaiements()->toArray(),
            fn(float $total, Paiement $p) => $total + $p->getMontant(),
            0
        );
    }

    public function getPaiementsAssocies(): array
    {
        $beneficiaire = $this->client ?? $this->fournisseur;
        return $beneficiaire ? $beneficiaire->getPaiements()->toArray() : [];
    }

    public function validateClientOrFournisseur(ExecutionContextInterface $context): void
    {
        if (!$this->client && !$this->fournisseur) {
            $context->buildViolation('Veuillez sélectionner un client ou un fournisseur.')
                ->atPath('client')
                ->addViolation();
        }

        if ($this->client && $this->fournisseur) {
            $context->buildViolation('Vous ne pouvez pas sélectionner à la fois un client et un fournisseur.')
                ->atPath('client')
                ->addViolation();
        }
    }
    #[ORM\ManyToOne(targetEntity: CommandeAchat::class, inversedBy: 'paiements')]
    #[ORM\JoinColumn(nullable: true)]
    private ?CommandeAchat $commandeAchat = null;

    #[ORM\ManyToOne(targetEntity: CommandeVente::class, inversedBy: 'paiements')]
    #[ORM\JoinColumn(nullable: true)]
    private ?CommandeVente $commandeVente = null;
    public function getCommandeAchat(): ?CommandeAchat
    {
        return $this->commandeAchat;
    }

    public function setCommandeAchat(?CommandeAchat $commandeAchat): static
    {
        $this->commandeAchat = $commandeAchat;
        return $this;
    }

    public function getCommandeVente(): ?CommandeVente
    {
        return $this->commandeVente;
    }

    public function setCommandeVente(?CommandeVente $commandeVente): static
    {
        $this->commandeVente = $commandeVente;
        return $this;
    }
    public function getTypePaiement(): string
    {
        if ($this->commandeVente) {
            return 'Client';
        } elseif ($this->commandeAchat) {
            return 'Fournisseur';
        }

        return $this->typePaiement; // Retourne la valeur stockée si aucune commande associée
    }
    public function setTypePaiement(?string $typePaiement): self
    {
        $this->typePaiement = $typePaiement;
        return $this;
    }
    // src/Entity/Paiement.php
    public function getResteAPayer(): ?float
    {
        $commande = $this->getCommandeVente() ?? $this->getCommandeAchat();
        if (!$commande) {
            return null;
        }

        $totalCommande = $commande->getTotalCommande();
        $montantPaye = array_reduce(
            $commande->getPaiements()->toArray(),
            fn(float $total, Paiement $p) => $total + $p->getMontant(),
            0
        );

        return max($totalCommande - $montantPaye, 0); // Ne pas retourner de valeur négative
    }
    public function isLieACommande(): bool
    {
        return $this->commandeVente !== null || $this->commandeAchat !== null;
    }
}
