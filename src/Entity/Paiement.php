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

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $typePaiement = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $commentaire = null;

    #[ORM\ManyToOne(inversedBy: 'paiements')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Client $client = null;

    #[ORM\ManyToOne(inversedBy: 'paiements')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Fournisseur $fournisseur = null;

    #[ORM\ManyToOne(targetEntity: CommandeAchat::class, inversedBy: 'paiements')]
    #[ORM\JoinColumn(nullable: true)]
    private ?CommandeAchat $commandeAchat = null;

    #[ORM\ManyToOne(targetEntity: CommandeVente::class, inversedBy: 'paiements')]
    #[ORM\JoinColumn(nullable: true)]
    private ?CommandeVente $commandeVente = null;

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

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): static
    {
        $this->commentaire = $commentaire;
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

    public function getBeneficiaire(): ?string
    {
        return $this->getNomBeneficiaire();
    }

    public function getCommandeAssociee(): ?string
    {
        if ($this->commandeVente) {
            return 'Vente #' . $this->commandeVente->getId();
        }

        if ($this->commandeAchat) {
            return 'Achat #' . $this->commandeAchat->getId();
        }

        return 'Aucune';
    }

    public function getTypeAssocie(): string
    {
        return $this->commandeVente ? 'Client' : ($this->commandeAchat ? 'Fournisseur' : 'Inconnu');
    }

    public function getTypePaiement(): ?string
    {
        return $this->typePaiement;
    }


    public function setTypePaiement(?string $typePaiement): static
    {
        $this->typePaiement = $typePaiement;
        return $this;
    }

    public function getTotalPaiements(): float
    {
        $beneficiaire = $this->client ?? $this->fournisseur;

        if (!$beneficiaire || !method_exists($beneficiaire, 'getPaiements')) {
            return 0;
        }

        return array_reduce(
            $beneficiaire->getPaiements()->toArray(),
            fn(float $total, Paiement $p) => $total + $p->getMontant(),
            0
        );
    }

    public function getResteAPayer(): ?float
    {
        $commande = $this->commandeVente ?? $this->commandeAchat;

        if (!$commande) {
            return null;
        }

        $totalCommande = $commande->getTotalCommande();

        $montantPaye = array_reduce(
            $commande->getPaiements()->toArray(),
            fn(float $total, Paiement $p) => $total + $p->getMontant(),
            0
        );
        dump([
            'totalCommande' => $totalCommande,
            'montantPaye' => $montantPaye,
            'reste' => $totalCommande - $montantPaye,
        ]);
        return max($totalCommande - $montantPaye, 0);
    }

    public function isLieACommande(): bool
    {
        return $this->commandeVente !== null || $this->commandeAchat !== null;
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

    public function __toString(): string
    {
        $date = $this->getDate()?->format('d/m/Y') ?? 'N/A';
        $montant = $this->montant ?? 0;
        $id = $this->id ?? 'N/A';

        return sprintf('Paiement #%s - %s - %.2f€', $id, $date, $montant);
    }
}
