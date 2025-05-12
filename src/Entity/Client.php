<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClientRepository::class)]
class Client
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adresse = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $contact = null;

    #[ORM\Column]
    private ?float $solde = null;

    #[ORM\Column]
    private ?float $credit = null;

    #[ORM\OneToMany(mappedBy: 'client', targetEntity: Paiement::class)]
    private Collection $paiements;

    #[ORM\OneToMany(mappedBy: 'client', targetEntity: CommandeVente::class)]
    private Collection $commandesVente;

    public function __construct()
    {
        $this->paiements = new ArrayCollection();
        $this->commandesVente = new ArrayCollection();
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): static
    {
        $this->adresse = $adresse;
        return $this;
    }

    public function getContact(): ?string
    {
        return $this->contact;
    }

    public function setContact(?string $contact): static
    {
        $this->contact = $contact;
        return $this;
    }

    public function __toString()
    {
        return $this->nom;
    }

    public function getSolde(): ?float
    {
        return $this->solde;
    }

    public function setSolde(float $solde): static
    {
        $this->solde = $solde;
        return $this;
    }

    public function getCredit(): ?float
    {
        return $this->credit;
    }

    public function setCredit(float $credit): static
    {
        $this->credit = $credit;
        return $this;
    }

    /**
     * @return Collection<int, Paiement>
     */
    public function getPaiements(): Collection
    {
        return $this->paiements;
    }

    public function addPaiement(Paiement $paiement): static
    {
        if (!$this->paiements->contains($paiement)) {
            $this->paiements[] = $paiement;
            $paiement->setClient($this);
        }

        return $this;
    }

    public function removePaiement(Paiement $paiement): static
    {
        if ($this->paiements->removeElement($paiement)) {
            if ($paiement->getClient() === $this) {
                $paiement->setClient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CommandeVente>
     */
    public function getCommandesVente(): Collection
    {
        return $this->commandesVente;
    }

    public function addCommandeVente(CommandeVente $commande): static
    {
        if (!$this->commandesVente->contains($commande)) {
            $this->commandesVente[] = $commande;
            $commande->setClient($this);
        }

        return $this;
    }

    public function removeCommandeVente(CommandeVente $commande): static
    {
        if ($this->commandesVente->removeElement($commande)) {
            if ($commande->getClient() === $this) {
                $commande->setClient(null);
            }
        }

        return $this;
    }
}
